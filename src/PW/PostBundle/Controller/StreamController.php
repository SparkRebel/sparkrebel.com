<?php

namespace PW\PostBundle\Controller;

use PW\ApplicationBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use PW\ItemBundle\Document\Item;
use PW\PostBundle\Document\Post;
use PW\PostBundle\Document\PostComment;
use PW\PostBundle\Form\Type\CommentFormType;

class StreamController extends AbstractController
{
    /**
     * @Method({"GET","POST"})
     * @Route("/stream/{type}/{timestamp}", defaults={"type" = "userAnon", "timestamp" = null}, name="stream")
     * @Template("PWPostBundle:Stream:stream.html.twig")
     */
    public function streamAction(Request $request, $type = 'userAnon', $timestamp = null, $limit = null, $id = null)
    {
        $redisQueueSize = $this->container->getParameter('redis.max_queue_size', 100);
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $me = $this->get('security.context')->getToken()->getUser();

        if (empty($id)) {
            $id = $request->query->get('id');
        }

        if (empty($limit)) {
            $limit = $request->query->get('limit', $this->container->getParameter('pw_post.stream_page_size'));
        }

        if ($timestamp) {
            $timestamp = new \MongoDate($timestamp);
        }

        $qb = $this->getQueryBuilder($timestamp, $limit, $dm);
        $return = array(
            'type'          => $type,
            'limit'         => $limit,
            'id'            => $id,
            'streamPending' => false,
        );

        $showBanner = false;
        $bannersConditions = array();
        
        $promoManager = $this->get('pw_promo.promo_manager');
        $showPromos = false;
        $showPromosWithBanners = false;
        $promos = array();

        switch ($type) {
            case 'createdBy':
                if (empty($id)) {
                    throw new HttpException(400);
                } else {
                    $qb->field('createdBy.$id')->equals(new \MongoId($id));
                }
                break;

            case 'board':
                if (empty($id)) {
                    throw new HttpException(400);
                } else {
                    $qb->field('board.$id')->equals(new \MongoId($id));
                }
                break;

            case 'category':
                if (empty($id)) {
                    throw new HttpException(400);
                } else {
                    $showBanner = true;
                    $bannersConditions['category.$id'] = new \MongoId($id); // show banner for this stream
                    
                    if (!$timestamp) {
                        // on first "chunk of sparks" get all Promos for "Sales & Promos" Category
                        $categoryManager = $this->get('pw_category.category_manager');
                        $category = $categoryManager->getRepository()->find($id);
                        if ($category->getIsPromos()) {
                            $showPromos = true;
                            $promos = $promoManager->getPromosForStream('Shop');
                        }
                    }
                    $qb->field('category.$id')->equals(new \MongoId($id))
                       ->field('userType')->equals('user');
                }
                break;

            case 'userStream':
                $showBanner = true;
                $bannersConditions['inMyStream'] = true; // show banner for this stream

                $redis = $this->get('snc_redis.default');
                $ids   = $redis->zrange('stream:{' . $id . '}:user', 0, $redisQueueSize);
                if (!empty($ids)) {
                    $qb->field('id')->in($ids);
                } else {
                    // @todo Handle loading from db as well
                }

                if ($me instanceOf \PW\UserBundle\Document\User) {
                    // get Promos for Brands&Merchants of this User
                    $showPromosWithBanners = true; 
                    $promos = $promoManager->getPromosForStream('MyStream', $me);
                }
                break;

            case 'userCelebsStream':
                $showBanner = true;
                $bannersConditions['inMyCelebs'] = true; // show banner for this stream

                if ($request->get('celeb_id')) {
                    if ($request->get('celeb_id') == 'all-celebs') {
                        // all-celebs stream
                        $qb->field('isCeleb')->equals(true);;
                        $return['celeb_id'] = $request->get('celeb_id');
                    } else {
                        // specific celeb stream
                        $celebId = $request->get('celeb_id');
                        $qb->field('board.$id')->equals(new \MongoId($celebId));
                        $return['celeb_id'] = $celebId;
                    }
                } else {
                    $followRepo = $dm->getRepository('PWUserBundle:Follow');
                    $follows    = $followRepo->getCelebsThatUserFollows($me);
                    $boardIds   = array();
                    foreach ($follows as $board) {
                        $boardIds[] = new \MongoId($board->getId());
                    }
                    $qb->field('board.$id')->in($boardIds);
                }
                break;

            case 'brandStream':
                $showBanner = true;
                $bannersConditions['inMyBrands'] = true; // show banner for this stream

                if ($brandId = $request->get('brand_id')) {
                    if ($brandId == 'all-brands') {
                        // all-brands stream
                        $qb->field('userType')->in(array('brand', 'merchant'));
                        $return['brand_id'] = $brandId;
                    } else {
                        // specific brand stream
                        $qb->field('createdBy.$id')->equals(new \MongoId($brandId));
                        $return['brand_id'] = $brandId;
                        
                        if (!$timestamp) {
                            // on first "chunk of sparks" get Promos for this Brand
                            $showPromos = true;
                            $promos = $promoManager->getPromosForStream(null, null, $brandId);
                        }
                    }
                } else {
                    // user brands stream
                    //$redis = $this->get('snc_redis.default');
                    //$ids   = $redis->zrange('stream:{' . $id . '}:brand', 0, $redisQueueSize);
                    //if (!empty($ids)) {
                    //    $qb->field('id')->in($ids);
                    //} else {
                    //    // @todo Handle loading from db as well
                    //}
                    
                    $followRepo = $dm->getRepository('PWUserBundle:Follow');
                    $follows = $followRepo->findFollowingByUser($me, 'users')->limit(100);
                    $follows->field('isActive')->equals(true);
                    $follows->field('isCeleb')->in(array(null, false));
                    $follows->field('target.type')->in(array('brand','merchant'));
                    $follows = $follows->getQuery()->execute();              
                    if (count($follows)>0) {
                        $ids = array();
                        foreach ($follows as $follow) {
                            $ids[] = new \MongoId($follow->getTarget()->getId());
                        }
                        $qb->field('createdBy.$id')->in($ids);
                        $qb->field('isCeleb')->in(array(null, false));
                    } else {
                        $qb->field('userType')->in(array('brand', 'merchant'));
                    }
                                    
                    if (!$timestamp && $me instanceOf \PW\UserBundle\Document\User) {
                        // on first "chunk of sparks" get Promos for Brands&Merchants of this User
                        $showPromos = true;
                        $promos = $promoManager->getPromosForStream('MyBrands', $me);
                    }
                }
                break;

            case 'brandAnon':
                $showBanner = true;
                $bannersConditions['inMyBrands'] = true; // show banner for this stream

                $qb->field('userType')->in(array('brand', 'merchant'));
                break;

            case 'onsaleStream':
                $redis = $this->get('snc_redis.default');
                $ids   = $redis->zrange('stream:{' . $id . '}:onsale', 0, $redisQueueSize);
                if (!empty($ids)) {
                    $qb->field('id')->in($ids);
                } else {
                    // @todo Handle loading from db as well
                }
                break;

            case 'brandOnsale':
                /*$redis = $this->get('snc_redis.default');
                $ids   = $redis->zrange('stream:{' . $id . '}:brandOnsale', 0, $redisQueueSize);
                if (!empty($ids)) {
                    $qb->field('id')->in($ids);
                } else {
                    // @todo Handle loading from db as well
                }
                break;*/
                $osqb = $dm->getRepository('PWItemBundle:Item')
                    ->findBy(array('isOnSale' => true, 'createdBy.$id'=>new \MongoId($id)))
                    ->sort(array('created' => 'desc'))
                    ->hint(array('created' => -1, 'isOnSale' => 1, 'createdBy.$id' => 1)) // point mongo to right index
                    ->limit($limit);
                $ids = array();
                foreach ($osqb as $item) {
                    $ids[] = $item->getRootPost()->getId();
                }
                $qb->field('id')->in($ids);
                break;

            case 'onsaleAnon':
                $osqb = $dm->getRepository('PWItemBundle:Item')
                    ->findBy(array('isOnSale' => true))
                    ->sort(array('created' => 'desc'))
                    ->limit($redisQueueSize);
                $ids = array();
                foreach ($osqb as $item) {
                    $ids[] = $item->getRootPost()->getId();
                }
                $qb->field('id')->in($ids);
                break;

            case 'userAnon':
            default:
                $showBanner = true;
                $bannersConditions['inAllCategories'] = true; // show banner for this stream

                // get all Promos for Brands&Merchants to show with Banners
                $showPromosWithBanners = true; 
                $promos = $promoManager->getPromosForStream('AllCategories');
                
                $qb->field('isCeleb')->in(array(null, false))
                   ->field('userType')->equals('user');
                
                // exclude Posts from "Other" Category
                $categoryManager = $this->get('pw_category.category_manager');
                $otherCategory = $categoryManager->getRepository()->findByName('Other')->getNext();
                if ($otherCategory) {
                    $qb->field('category.$id')->notEqual(new \MongoId($otherCategory->getId()));
                }
                
                break;
        }

        $posts = $qb->getQuery()->execute();
        if (empty($posts)) {
            $posts = $this->getQueryBuilder($timestamp, $limit, $dm)
               ->field('userType')->equals('user')
               ->getQuery()->execute();
            $return['streamPending'] = true;
            $this->get('pw.event')->requestJob("stream:refresh {$me->getId()} user", 'medium', '', '', 'feeds');
        }

        $friends = new \Doctrine\Common\Collections\ArrayCollection;
        if ($me instanceOf \PW\UserBundle\Document\User) {
            $form = $this->createForm(new CommentFormType(false));
            $return['form'] = $form->createView();
            $return['formInstance'] = $form;
            $friends = $this->get('pw_user.user_manager')->getFriendsIdsForUser($me);
        }

        $return['posts']   = $posts;
        $return['friends'] = $friends;

        //
        // Fashion Week(s)
        // if (in_array($type, array('userAnon', 'userStream'))) {
        //     // Get System User
        //     $username = $this->container->getParameter('pw.system_user.sparkrebel.username');
        //     $user     = $this->getUserManager()->getRepository()->findOneByUsername($username);
        //     $return['fw_board'] = $this->getBoardManager()
        //         ->getRepository()
        //         ->createQueryBuilder()
        //         ->field('name')->equals('Paris Fashion Week')
        //         ->field('createdBy')->references($user)
        //         ->getQuery()
        //         ->getSingleResult();
        // }

        // handle Banner
        $return['banner'] = null;
        $return['showBanner'] = false;
        if ($showBanner) {
            $bannerManager = $this->get('pw_banner.banner_manager');
            $banner_result = array();
            $bannersConditions['isActive'] = true;
            $bannersConditions['endDate']['$gte'] = new \MongoDate();
            $bannersConditions['startDate']['$lte'] = new \MongoDate();
            
            // check if this is next banner
            if ($last_banner = $request->get('last_banner')) {
                // get next banner
                $c = $bannersConditions;
                $c['id']['$gt'] = $last_banner;
                $banner_result = $bannerManager->getRepository()
                    ->findBy($c)
                    ->sort(array('id' => 'asc'))
                    ->limit(1);
            }
            if (count($banner_result)<1) {
                // we start from beginning or from random place
                $c = $bannersConditions;
                $banner_result = $bannerManager->getRepository()
                    ->findBy($c)
                    ->sort(array('id' => 'asc'))
                    ->limit(1);
                if (!$last_banner) {
                    // we start from random place
                    $banner_result->skip(rand(0,count($banner_result)-1));
                }
            }
            if (count($banner_result)>0) {
                $return['banner'] = $banner_result->getNext();
                $return['showBanner'] = true;
            }
        }
        
        // show Promos on top?
        $return['promos'] = null;
        if ($showPromos) {
            $return['promos'] = $promos;
        }
        // show one Promo with Banner?
        $rand = (rand(0,10)<=5);
        if ($showPromosWithBanners && ($rand || !$return['showBanner'])) {
            $results = array();
            foreach ($promos as $p) {
                $results[] = $p;
            } 
            if (count($results)) {
                $return['promos'] = array( $results[rand(0,count($results)-1)] );
                $return['showBanner'] = false;
            }
        }

        return $return;
    }

    public function getQueryBuilder($timestamp, $limit, $dm)
    {
        return $dm->getRepository('PWPostBundle:Post')
            ->findLatest($timestamp, $limit)
            ->field('recentActivity')->prime(true)
            ->eagerCursor(true);
    }

    public function arrangePostsByHeight(&$postHeightsOrder, &$return, $fill)
    {
        $keys = array_keys($postHeightsOrder);

        $return[$fill][] = $postHeightsOrder[$keys[0]];
        unset($postHeightsOrder[$keys[0]]);

        $lastKey = count($keys)-1;
        if(isset($postHeightsOrder[$keys[$lastKey]]) && count($postHeightsOrder) > 1) {
            $return[$fill][] = $postHeightsOrder[$keys[$lastKey]];
            unset($postHeightsOrder[$keys[$lastKey]]);
        }

        if(count($postHeightsOrder) > 0) {
            $fill = ($fill == 'leftPosts') ? 'rightPosts' : 'leftPosts';

            $this->arrangePostsByHeight($postHeightsOrder, $return, $fill);
        }
    }

    /**
     * @Template
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function newsletterUserStreamAction($user, $tracking)
    {
        /**
         * workaround for twig:render in template
         */
        $this->get('router')->getContext()->setHost($this->container->getParameter('host'));

        $redisQueueSize = $this->container->getParameter('redis.max_queue_size', 100);
        $dm = $this->get('doctrine_mongodb.odm.document_manager');

        $now = new \DateTime();
        $timestamp = new \MongoDate($now->getTimestamp());

        $qb = $this->getQueryBuilder($timestamp, $redisQueueSize, $dm);

        $redis = $this->get('snc_redis.default');
        $ids   = $redis->zrange('stream:{' . $user->getId() . '}:user', 0, $redisQueueSize);
        if (!empty($ids)) {
            $qb->field('id')->in($ids);
        }

        $posts = $qb->getQuery()->execute();

        $uniquePosts = array();

        foreach($posts as $post) {

            $isRespark = false;

            if(count($uniquePosts) > 0) {
                foreach($uniquePosts as $uniquePost) {
                    $isRespark = $post->isResparkOfViaAsset($uniquePost);                    
                }
            }

            if(!$isRespark && $post->getGlobalRepostCount() > 0) {
                $uniquePosts[$post->getId()] = $post;
            }

            if(count($uniquePosts) == 6) {
                break;
            }
        }

        //layout arrangement of posts, by height
        $postHeights = $postHeightsOrder = array();
        $fullWidth = 266;


        foreach($uniquePosts as $id => $post) {
            $originalDimensions = $post->getImage()->getOriginalDimensions();
            $height = $originalDimensions['height'];
            $width = $originalDimensions['width'];

            //using the real ratio of displaying images against the fullWidth
            $showingHeight = round((($fullWidth * 100) / $width) * $height);

            $postHeights[$id] = $showingHeight;
        }

        asort($postHeights);

        foreach($postHeights as $id => $height) {

            $postHeightsOrder[] = $uniquePosts[$id];
        }

        $this->arrangePostsByHeight($postHeightsOrder, $return, 'leftPosts');

        $return['tracking'] = $tracking;

        return $return;
    }

    /**
     * @Template
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function newsletterBrandsStreamAction($user, $tracking)
    {
        /**
         * workaround for twig:render in template
         */
        $this->get('router')->getContext()->setHost($this->container->getParameter('host'));

        $redisQueueSize = $this->container->getParameter('redis.max_queue_size', 100);
        $dm = $this->get('doctrine_mongodb.odm.document_manager');

        $now = new \DateTime();
        $timestamp = new \MongoDate($now->getTimestamp());

        //getting brands posts
        $followRepo = $dm->getRepository('PWUserBundle:Follow');
        $follows = $followRepo->findFollowingByUser($user, 'users')->limit(100);
        $follows->field('isActive')->equals(true);;
        $follows->field('isCeleb')->in(array(null, false));
        $follows->field('target.type')->in(array('brand','merchant'));
        $follows = $follows->getQuery()->execute(); 
        $brandPostResults = array();        
        if (count($follows)>0) {
            $ids = array();
            foreach ($follows as $follow) {
                $ids[] = new \MongoId($follow->getTarget()->getId());
            }
            $qb = $this->getQueryBuilder($timestamp, 50, $dm);
            $qb->field('createdBy.$id')->in($ids);
            $qb->field('isCeleb')->in(array(null, false));
            $brandPostResults = $qb->getQuery()->execute();
        }

        $brandsPosts = array();
        $categories = array();

        foreach($brandPostResults as $brandPostResult) {
            if($brandPostResult->getBoard()->getCategory() && !in_array($brandPostResult->getBoard()->getCategory()->getId(), $categories)) {
                $categories[] = $brandPostResult->getBoard()->getCategory()->getId();
                $brandsPosts[] = $brandPostResult;

                if(count($brandsPosts) == 4) {
                    break;
                }
            }
        }

        $return['brandsPosts'] = $brandsPosts;

        //getting on sale posts
        $qb = $this->getQueryBuilder($timestamp, $redisQueueSize, $dm);

        $redis = $this->get('snc_redis.default');
        $ids   = $redis->zrange('stream:{' . $user->getId() . '}:onsale', 0, $redisQueueSize);
        if (!empty($ids)) {
            $qb->field('id')->in($ids);
        }

        $brandOnSalePostResults = $qb->getQuery()->execute();

        $brandsOnSalePosts = array();
        $categories = array();

        foreach($brandOnSalePostResults as $brandOnSalePostResult) {
            if($brandOnSalePostResult->getBoard()->getCategory() && !in_array($brandOnSalePostResult->getBoard()->getCategory()->getId(), $categories)) {
                $categories[] = $brandOnSalePostResult->getBoard()->getCategory()->getId();
                $brandsOnSalePosts[] = $brandOnSalePostResult;

                if(count($brandsOnSalePosts) == 4) {
                    break;
                }
            }
        }

        $return['brandsOnSalePosts'] = $brandsOnSalePosts;

        $return['tracking'] = $tracking;

        return $return;
    }
}
