<?php

namespace PW\CategoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PW\PostBundle\Form\Type\CommentFormType;

class DefaultController extends Controller
{
    /**
     * @Route("/channels/", name="board_categories")
     * @Template
     */
    public function boardCategoriesAction()
    {
        $categories = $this->get('pw.category')->getCategories('user');

        return array(
            'categories' => $categories
        );
    }

    /**
     * @Method({"GET", "POST"})
     * @Route("/channels/all-channels", name="board_category_view_all")
     * @Template("PWCategoryBundle:Default:all.html.twig")
     */
    public function viewAllCategoriesAction()
    {
        return array(
            'title' => 'latest sparks in all categories',
        );
    }

    /**
     * @Route("/channels/{slug}", name="board_category_view")
     * @Template("PWCategoryBundle:Default:stream.html.twig")
     */
    public function boardCategoryAction($slug)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $categoryRepo = $dm->getRepository('PWCategoryBundle:Category');
        $category = $categoryRepo->findOneBy(
            array(
                'type' => 'user',
                'slug' => $slug,
                'isActive' => true
            )
        );

        $log_date = '['. date('Y-m-d H:i:s') .']';
        file_put_contents('/tmp/channels_views.log', $log_date.' category: '.$slug."\n", FILE_APPEND); // log for now
        
        if (empty($category)) {
            throw $this->createNotFoundException("Category not found");
        }
        
        // get image for Facebook "og:image"
        $timestamp = new \MongoDate(time());
        $qb = $dm->getRepository('PWPostBundle:Post')
            ->findLatest($timestamp, 1)
            ->field('recentActivity')->prime(true)
            ->eagerCursor(true);
        $qb ->field('category.$id')->equals(new \MongoId($category->getId()))
            ->field('userType')->equals('user');
        $latestPosts = $qb->getQuery()->execute();

        return array('category' => $category, 'latestPosts' => $latestPosts);
    }
}
