<?php

namespace PW\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    protected function doSearch(Request $request, $collectionType)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
    	$serializer = $this->get('serializer');

    	$get = $request->query->all();

    	$userVIP = function($item){return $item->hasRole('ROLE_PARTNER');};

    	$searchTypes = array(
            'board' => array('board', 'PWBoardBundle:Board', 'name', function($item){return $item->getCreatedBy()->hasRole('ROLE_PARTNER');}, false),
            // 'item' => array('item', 'PWItemBundle:Item', 'name', false, false),
            'post' => array('post', 'PWPostBundle:Post', 'description', false, array('userType' => 'user')),
            'user' => array('user', 'PWUserBundle:User', 'name', $userVIP, array('type' => 'user')),
            'brand' => array('user', 'PWUserBundle:Brand', 'name', $userVIP, array('type' => 'brand')),
            'merchant' => array('user', 'PWUserBundle:Merchant', 'name', $userVIP, array('type' => 'merchant')),
    	);

    	if($collectionType) {
    	    $ctm = array(
    	        'boards' => 'board',
                // 'items' => 'item',
    	        'posts' => 'post',
    	        'users' => 'user',
    	        'brands' => 'brand',
    	        'merchants' => 'merchant',
    	    );

    	    $doSearch = array($ctm[$collectionType]);
    	} else {
    	    $doSearch = array_keys($searchTypes);
    	}

    	$r = array();
    	foreach ($doSearch as $type) {
    	    $typeData = $searchTypes[$type];
    	    list($searchCollection, $itemClass, $searchField, $hasVIP, $searchTerms) = $typeData;

    	    $repo = $dm->getRepository($itemClass);

    	    $terms = array(
    	        'size' => 100,
    	        'query' => array(
    	            'bool' => array(
    	                'must' => array(
    	                    array('term' => array('isActive' => true)),
    	                    array('text' => array(
    	                        $searchField => array(
    	                            'query' => $get['q'],
    	                            'operator' => 'and',
    	                        ),
    	                    )),
    	                ),
    	            ),
    	        ),
    	    );

    	    if ($searchTerms) {
    	        $terms['query']['bool']['must'][] = array('term' => $searchTerms);
    	    }

    	    $result = $this->get('pw.search')->esSearch($repo, $searchCollection, $terms);

    	    foreach($result['results'] as $item) {
    	        $isVIP = ($hasVIP ? $hasVIP($item) : false);
    	        $vipField = ($isVIP ? 'vip' : 'standard');

    	        $r[$type][$vipField][] = $item;
    	    }
    	}
        return array(
            'results' => $r,
            'searchQuery' => $get['q'],
        );
    }

    /**
     * @Route("/search")
     * @Template
     */
    public function indexAction(Request $request)
    {
        return $this->doSearch($request, false);
    }

    /**
     * @Route("/search/{collectionType}",
     *      requirements={
     *          "collectionType"="boards|items|posts|users|brands|merchants"
     *      }
     * )
     * @Template("PWSearchBundle:Default:index.html.twig")
     */
     public function sectionSearchAction(Request $request, $collectionType)
     {
        return $this->doSearch($request, $collectionType);
     }
}
