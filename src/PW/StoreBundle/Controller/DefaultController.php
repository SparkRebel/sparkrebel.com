<?php

namespace PW\StoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Gedmo\Sluggable\Util\Urlizer;

function mb_strcasecmp($str1, $str2, $encoding = null)
{
    if (null === $encoding) {
        $encoding = mb_internal_encoding();
    }

    return strcmp(mb_strtoupper($str1, $encoding), mb_strtoupper($str2, $encoding));
}

class DefaultController extends Controller
{
    protected $categories;
    protected $allCats;

    protected function _generateCategories()
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');

        $cats = array();
        $allCats = array();

        foreach($dm->getRepository('PWCategoryBundle:Category')->findByType('item')->getQuery()->execute() as $item) {
            $cats[$item->getSlug()] = array(
                'id' => $item->getId(),
                'name' => $item->getName(),
                'slug' => $item->getSlug(),
                'parent' => $item->getParent(),
                'subcategories' => array(),
            );
        }

        foreach($cats as $k => &$v) {
            if($v['parent']) {
                $slug = $v['parent']->getSlug();

                $v['parent'] = $slug;

                $cats[$slug]['subcategories'][$k] = $v;
            }

            $allCats[$k] = $v;

            if ($v['parent']) {
                unset($cats[$k]);
            }
        }
        unset($v);

        $this->categories = $cats;
        $this->allCats = $allCats;
    }

    protected function _getCategories()
    {
        if (empty($this->categories)) {
             $s = $this->get('cache.apc')->fetch('sr_store_categories');
             if ($s) {
                $s = unserialize($s);

                $this->categories = $s['categories'];
                $this->allCats = $s['allCats'];
            } else {
                $this->_generateCategories();

                $s = serialize(array(
                    'categories' => $this->categories,
                    'allCats' => $this->allCats,
                ));
                $this->get('cache.apc')->save('sr_store_categories', $s, 1800); //30m
            }
        }

        return $this->categories;
    }

    protected function _prepareSearch(Request $request, $type)
    {
        $post = $request->request->all();

        if (empty($post['search'])
            || !is_array($post['search'])
        ) {
            return false;
        }

        $search = array_merge(array(
            'category' => '',
            'subcategory' => '',
            'searchFilter' => 'all',
            'searchText' => '',
            'price' => false,
            'from' => 0,
            'size' => 30,
        ), $post['search']);

        $response = array(
            'items' => array(),
        );

        $crit = array(
            array('term' => array('isActive' => true)),
        );

        $searchText = trim($search['searchText']);
        if(!empty($searchText)) {
            $crit[] = array('query' => array('text' => array('_all' => array(
                'query' => $searchText,
                'operator' => 'and',
            ))));
        }

        $searchFilter = $search['searchFilter'];
        //searchFilter = (all | new | sale)
        if ($searchFilter == 'new') {
            //asssume new means last week
            $crit[] = array(
                'numeric_range' => array(
                    'created' => array('gt' => time() - (7 * 86400)),
                ),
            );
        } else if($searchFilter == 'sale') {
            $crit[] = array('term' => array('isOnSale' => true));
        } else { //all
        }

        //Categories - $search['category'] and $search['subcategory']
        $cat = $search['category'];
        $subcat = $search['subcategory'];

        if ($subcat || $cat) {
            $cats = $this->_getCategories();

            $catSearch = array();

            if ($subcat) {
                $catSearch[] = $this->allCats[$subcat]['id'];
            } else if ($cat) {
                if (isset($cats[$cat])) {
                    $catSearch[] = $cats[$cat]['id'];

                    foreach($cats[$cat]['subcategories'] as $subcat) {
                        $catSearch[] = $subcat['id'];
                    }
                }
            }

            $crit[] = $this->get('pw.search')->makeSimpleOrFilter('categories', $catSearch);
        }

        //Price
        if (!empty($search['price'])) {
            $price = explode(',', $search['price']);

            if (count($price) == 2 && is_numeric($price[0]) && is_numeric($price[1])) {
                $crit[] = array(
                    'numeric_range' => array(
                        'price' => array(
                            'gte' => (float)$price[0],
                            'lte' => (float)$price[1],
                        ),
                    ),
                );
            }
        }

        if ($type != 'data') {
            //Brand
            if (!empty($search['store'])) {
                $store = explode(',', $search['store']);
                array_walk($store, function(&$v) { $v = pack("H*" , $v); }); //no hex2bin in PHP5!

                $crit[] = $this->get('pw.search')->makeSimpleOrFilter('bmNames', $store);
            }
        }

        $es = array(
            'from' => 0,
            'size' => 100,
            'query' => array(
                'filtered' => array(
                    'filter' => array(),
                    'query' => array('match_all' => array()),
                ),
            ),
        );

        if ($type == 'search') {
            $from = (int)$search['from'];
            $size = (int)$search['size'];

            if ($from < 0) {
                $from = 0;
            }

            if (!in_array($size, array(30, 60, 90))) {
                $size = 30;
            }

            $es['from'] = $from;
            $es['size'] = $size;
        }

        $cnt = count($crit);
        if ($cnt == 0) {
            $es['query'] = $es['query']['filtered']['query'];
        } else if ($cnt == 1) {
            $es['query']['filtered']['filter'] = $crit[0];
        } else {
            $es['query']['filtered']['filter']['and'] = $crit;
        }

        return $es;
    }

    /**
     * @Route(
     *      "/shop/data",
     *       defaults = {"_format" = "json"},
     *       requirements = {
     *           "_format" = "json"
     *       }
     * )
     *
     * Search the items with the specified criteria and return the distinct
     * brands and merchants, and an item count.
     */
    public function dataAction(Request $request)
    {
        $crit = $this->_prepareSearch($request, 'data', false);

        $es = array(
            'size' => 0,
            'query' => array('query_string' => array('query' => '*')),
            'facets' => array(
                'storeBrands' => array(
                    'terms' => array(
                        'field' => 'bmNames',
                        'size' => 1000,
                    ),
                ),
            ),
        );

        if (!empty($crit['query']['filtered']['filter'])) {
            $es['facets']['storeBrands']['facet_filter'] = $crit['query']['filtered']['filter'];
        }

        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $repo = $dm->getRepository('PWUserBundle:User');

        $esResults = $this->get('pw.search')->esRawSearch('item', $es);
        $facets = $esResults->getFacets();

        $data = array();
        foreach ($facets['storeBrands']['terms'] as $r) {
            $name = $r['term'];
            $id = bin2hex($name);

            $data[$id] = array(
                'id' => $id,
                'name' => $name,
                'count' => $r['count'],
            );
        }

        uasort($data, function($a, $b) { return mb_strcasecmp($a['name'], $b['name']); });

        return new Response(json_encode(array(
            'store' => $data,
        )));
    }

    /**
     * @Route("/shop/search",
     *       defaults = {"_format" = "json"},
     *       requirements = {
     *           "_format" = "json"
     *       }
     * )
     */
    public function searchAction(Request $request)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $itemsRepo = $dm->getRepository('PWItemBundle:Item');
        $crit = $this->_prepareSearch($request, 'search');
        $esResult = $this->get('pw.search')->esSearch($itemsRepo, 'item', $crit);

        $response = array(
            'from' => $crit['from'],
            'size' => $crit['size'],
            'total' => $esResult['total'],
            'items' => array(),
        );

        foreach($esResult['results'] as $item) {
            $post = $item->getRootPost();

            if (!$post) {
                $sparkCount = 0;
                $buyUrl = '';
                $postUrl = '';
                $repostUrl = '';
            } else {
                $postID = $post->getId();

                $sparkCount = $post->getRepostCount();
                $buyUrl = $post->getLink();
                $postUrl = $this->generateUrl(
                    'pw_post_default_view',
                    array(
                        'id' => $postID,
                        'slug' => Urlizer::urlize($post->getDescription())
                    )
                );

                $repostUrl = $this->generateUrl(
                    'post_add_index',
                    array(
                        'type' => 'repost',
                        'id' => $postID,
                    )
                );
            }

            $image = $item->getImagePrimary();
            if($image) {
                $imageURL = $image->getUrl();
            } else {
                $imageURL = '';
            }

            $response['items'][] = array(
                'id' => $item->getId(),
                'name' => $item->getName(),
                'description' => $item->getDescription(),
                'price' => $this->get('twig.extension.craue_formatNumber')->formatCurrency($item->getPrice()),
                'imagePrimary' => $imageURL,
                'buyUrl' => $buyUrl,
                'postUrl' => $postUrl,
                'repostUrl' => $repostUrl,
                'sparkCount' => $sparkCount,
                'merchant' => $item->getMerchantName(),
            );
        }

        return new Response(json_encode($response));
    }

    /**
     * @Route("/shop/{category}/{subcategory}",
     *      defaults = {"category":"", "subcategory":""}
     * )
     * @Template("PWStoreBundle:Default:index.html.twig")
     */
    public function indexAction(Request $request, $category, $subcategory)
    {
        $categories = $this->_getCategories();

        $initialSearch = array();
        if ($category) {
            if (isset($categories[$category])) {
                $initialSearch['category'] = $category;

                if($subcategory) {
                    if(isset($categories[$category]['subcategories'][$subcategory]))
                        $initialSearch['subcategory'] = $subcategory;
                }
            } else {
            }
        }

        $validParams = array_flip(array('searchFilter', 'searchText', 'price', 'store'));
        foreach($request->query->all() as $k => $v) {
            if (isset($validParams[$k])) {
                if($v = trim($v)) {
                    $initialSearch[$k] = $v;
                }
            }
        }

        return array(
            'storeInit' => json_encode(array(
                'basePath' => $this->generateUrl('pw_store_default_index'),
                'categories' => $categories,
                'initialSearch' => (object)$initialSearch,
            )),
        );
    }
}
