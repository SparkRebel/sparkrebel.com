<?php

namespace PW\PostBundle\Controller;

use PW\ApplicationBundle\Controller\AbstractController,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    JMS\SecurityExtraBundle\Annotation\Secure,
    Gedmo\Sluggable\Util\Urlizer,
    PW\PostBundle\Document\Post,
    PW\PostBundle\Form\Type\CreatePostType,
    PW\PostBundle\Form\Type\CreateRepostType,
    PW\PostBundle\Form\Model\CreatePost,
    PW\PostBundle\Form\Model\CreateRepost,
    PW\BoardBundle\Document\Board,
    PW\BoardBundle\Form\Type\CreateBoardType,
    PW\BoardBundle\Form\Type\CreateRepostBoardType,
    PW\BoardBundle\Form\Model\CreateBoard;

/**
 * AddController
 */
class AddController extends AbstractController
{
    /**
     * @Secure(roles="ROLE_USER")
     * @Route("/posts/add/{type}", defaults={"type" = null}, name="post_add_index")
     * @Template
     */
    public function indexAction(Request $request, $type)
    {
        /* @var $postManager \PW\PostBundle\Model\PostManager */
        $postManager = $this->get('pw_post.post_manager');
        $result      = array('success' => false);
        $me          = $this->get('security.context')->getToken()->getUser();
        $query       = $request->query;
        $posts       = $this->_createPostsByType($type, $query);
        $dm = $this->get('doctrine_mongodb.odm.document_manager');

        $userSettings = $me->getSettings();

         // if we had problems with loading assets, show user the error page
        if(count($posts) === 0) {
          return $this->render('PWPostBundle:Add:error.html.twig', array('popup' => $query->has('popup')));
        }
        
        $postOnFacebook = true;
        if ($this->get('session')->get('unchecked_postOnFacebook',false)) {
            $postOnFacebook = !empty($userSettings['postOnFacebook']);
        }

        if ($type === 'repost') {
            $postForm = $this->createForm(
                new CreateRepostType($me, $postOnFacebook),
                new CreatePost(reset($posts))
            );    
            
        } else {
            $postForm = $this->createForm(
                new CreatePostType($me, $type == 'multiasset', $postOnFacebook),
                new CreatePost(reset($posts))
            );               
        }
     
        $boardForm = $this->createForm(
            new CreateBoardType(),
            new CreateBoard()
        );
    
        if ($request->getMethod() == 'POST') {
            $postForm->bindRequest($request);

            if ($postForm->isValid()) {
                $formData = $postForm->getData();
                $mainPost = $formData->getPost();                            

                $userSettings->setPostOnFacebook($formData->getPostOnFacebook());
                $dm->persist($me);
                if (!$formData->getPostOnFacebook()) {
                    // user unchecked postOnFacebook -> remember it so we will use value from user.settings in PW\PostBundle\Extension\Repost
                    $this->get('session')->set('unchecked_postOnFacebook', true);
                }

                foreach ($posts as $post /* @var $post \PW\PostBundle\Document\Post */) {
                    if (!$post->getBoard()) {
                        $post->setBoard($mainPost->getBoard());
                    }

                    if (!$post->getCategory()) {
                        $post->setCategory($mainPost->getCategory());
                    }

                    if (count($posts) == 1) {
                        $post->setPostOnFacebook($userSettings['postOnFacebook']);
                    }

                    if ($post->getImage()->getIsVideo()) {
                        $post->setIsVideoPost(true);
                    }

                    $originalPost = $post->getOriginal() ? $post->getOriginal()->getImage() : null ;
                    if ($originalPost && $type == 'repost' && $originalPost->isGetty()) {

                        $this->get('pw.stats')
                            ->record('repost', $originalPost, $me, $this->get('request')->getClientIp());
                    }

                    $postManager->save($post, array('validate' => false));

                    //picscout check here
                    $this->get('pw.event')->requestJob('pw:picscout:check '. $post->getImage()->getId());
                    //app/console
                }

                $firstPost = reset($posts);

                $attachment = false;
                if ($firstPost->getPostOnFacebook()) {
                    $attachment = $firstPost->getFacebookAttachment();
                    $attachment['link'] = $this->generateUrl(
                        'pw_post_default_view', array(
                            'id' => $post->getId(),
                            'slug' => Urlizer::urlize(Urlizer::transliterate($firstPost->getDescription())),
                            'utm_source' => 'facebook',
                            'utm_medium' => 'viral',
                            'utm_campaign' => 'fb_sharing'
                        ), true
                    );
                    $baseUrl = rtrim($this->generateUrl('home', array(), true), '/');
                    $attachment['icon'] = $baseUrl . 'images/icons/repost.png';
                    if (stripos($attachment['picture'], '://') === false) {
                        $attachment['picture'] = $baseUrl . $attachment['picture'];
                    }
                }

                $board    = $firstPost->getBoard();
                $category = $board->getCategory();

                $result = array(
                    'success'  => true,
                    'type'     => $type,
                    'id'       => $firstPost->getId(),
                    'board'    => array(
                        'id'   => $board ? $board->getId()   : null,
                        'name' => $board ? $board->getName() : null,
                        'category' => array(
                            'id'   => $category ? $category->getId()   : null,
                            'name' => $category ? $category->getName() : null,
                        ),
                        'url'  => $this->generateUrl('pw_board_default_view', array(
                            'id'   => $board ? $board->getId()   : null,
                            'slug' => $board ? $board->getSlug() : null,
                        )),
                    ),
                    'facebook_attachment' => $attachment,
                    'redirect' => $this->generateUrl('pw_post_default_view', array(
                        'id' => $firstPost->getId()
                    )),
                );
            } else {
                $result['error'] = $this->_getFirstErrorMessage($postForm);
            }

            if ($request->isXmlHttpRequest()) {
                $response = new Response(json_encode($result));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            } else {
                if ($result['success']) {
                    $this->get('session')->setFlash('success', sprintf("Spark%s posted successfully", count($posts) > 1 ? 's' : ''));
                    return $this->redirect($result['redirect']);
                } else {
                    $this->get('session')->setFlash('error', sprintf("Spark%s post failed", count($posts) > 1 ? 's' : ''));
                }
            }
        }

        $result = array(
            'me'               => $me,
            'posts'            => $posts,
            'popup'            => $query->has('popup'),
            'postForm'         => $postForm->createView(),
            'boardForm'        => $boardForm->createView(),
            'boards'           => $me->getBoards(),
            'boardCategoryMap' => json_encode($postManager->generateBoardCategoryMap($me)),
            'post_route'       => $this->generateUrl('post_add_index', array_merge(
                $query->all(), array('type' => $type)
            )),
        );

        if ($request->isXmlHttpRequest()) {
            if ($type === 'repost') {
                return $this->render('PWPostBundle:Add:partials/repostForm.html.twig', $result);
            } else {
                return $this->render('PWPostBundle:Add:partials/postForm.html.twig', $result);
            }
            
        } else {
            return $result;
        }
    }




    /**
     * @param string $type
     * @param type $query
     * @return array
     * @throws \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     */
    protected function _createPostsByType($type, $query)
    {
        /* @var $postManager \PW\PostBundle\Model\PostManager */
        $postManager = $this->get('pw_post.post_manager');

        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $this->dm = $dm;
        $me = $this->get('security.context')->getToken()->getUser();
        $posts = array();

        switch ($type) {

            case 'repost':
                $post  = $postManager->createRepost($query->get('id'), $me);
                if ($post->getImage()->getThumbsExtension() == 'png') {
                    $post->setImage( $this->get('pw.asset')->syncAsset($post->getImage(), $sync_now) );
                }
                $posts = array($post);
                break;

            case 'asset':
                /* @var $asset \PW\AssetBundle\Document\Asset */
                $asset = $this->get('pw.asset')->addImageFromUrl($query->get('image'), $query->get('url'), array(), false, true);
                if($asset instanceof \PW\AssetBundle\Document\Asset ) {
                  $post  = $postManager->createAssetPost($asset, $query->get('title'), $me);
                  $posts = array($post);
                }

                break;

            case 'upload':
                /* @var $asset \PW\AssetBundle\Document\Asset */
                $asset = $dm->getRepository('PWAssetBundle:Asset')->find($query->get('asset'));
                if($asset instanceof \PW\AssetBundle\Document\Asset ) {
                  $post  = $postManager->createAssetPost($asset, null, $me);
                  $posts = array($post);
                }

                break;

            case 'multiasset':
                if (!$query->has('url') || !$query->has('i')) {
                    throw new \Symfony\Component\Routing\Exception\MissingMandatoryParametersException();
                }
                set_time_limit(75); // may need longer than 30s if many assetss uploaded in one request
                $images = $query->get('i');
                foreach ($images as $i => $image) {
                    if (isset($image['src']) && !empty($image['src'])) {
                        /* @var $asset \PW\AssetBundle\Document\Asset */

                        $sparker = new \PW\PostBundle\Model\VideoSparker($image['src']);
                        if($sparker->isValidVideoUrl()) {
                            $asset = $this->get('pw.asset')->addVideoFromUrl($image['src']);
                        } else {
                            $asset = $this->get('pw.asset')->addImageFromUrl($image['src'], $query->get('url'), array(), false, true);
                        }


                        if($asset instanceof \PW\AssetBundle\Document\Asset ) { //check if asset was loaded
                          $post  = $postManager->createAssetPost($asset, null, $me);
                          if (isset($image['desc']) && !empty($image['desc'])) {
                              $post->setDescription($image['desc']);
                          }
                          // Maintain index association
                          $posts[$i] = $post;
                        }


                    }
                }
                break;


            case 'video':
                $asset = $this->get('pw.asset')->addVideoFromUrl($query->get('url'));
                if($asset instanceof \PW\AssetBundle\Document\Asset ) {
                    $post  = $postManager->createAssetPost($asset, null, $me);
                    $posts = array($post);
                }
                break;
        }

        return $posts;
    }
    
    protected function syncAsset($asset)
    {
        $host = $this->container->getParameter('host');
        $dir = '..';
        if ($host == 'sparkrebel.com') {
            $dir = '/var/www/sparkrebel.com/current';
        } else if ($host == 'staging.sparkrebel.com') {
            $dir = '/var/www/staging.sparkrebel.com/current';
        }
        $command = "cd $dir && php app/console asset:sync {$asset->getId()} --verbose --env=prod >> app/logs/addController_syncAsset.log 2>&1";
        system($command);
        $this->dm->detach($asset);
        return $asset = $this->dm->getRepository('PWAssetBundle:Asset')->find($asset->getId());
    }
}
