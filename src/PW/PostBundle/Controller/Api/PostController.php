<?php

namespace PW\PostBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    FOS\RestBundle\Controller\Annotations as REST,
    FOS\Rest\Util\Codes,
    PW\ApiBundle\Response\ApiResponse;

/**
 * @todo Migrate '1.0' in URL to headers
 * @REST\Prefix("/1.0")
 * @REST\NamePrefix("api_")
 */
class PostController extends Controller
{
    /**
     * @REST\View
     */
    public function getSparkAction(Request $request, $id)
    {
        $post = $this->getPostManager()
            ->getRepository()
            ->find($id);

        $response = new ApiResponse($post);
        if (!$post) {
            return $response->setError('Spark not found', Codes::HTTP_NOT_FOUND);
        } elseif ($post->getDeleted()) {
            return $response->setError('Spark has been removed', Codes::HTTP_GONE);
        }

        $post->setCanonicalUrl($this->generateUrl('post_default_view', array('id' => $post->getId()), false));

        // More boards with this item
        $similarPosts = $this->getPostManager()
            ->getRepository()
            ->findByTarget($post->getTarget())
            ->getQuery()->execute();

        $allBoards = array();
        //logic for allBoards:
        // - 1st the current board
        // - then the parent board, if exists
        // - then the original board, if exists
        // - then the rest of the boards

        $allBoards[] = $post->getBoard();
        if ($post->getParent()) {
            $allBoards[] = $post->getParent()->getBoard();
        }
        if ($post->getOriginal()) {
            $allBoards[] = $post->getOriginal()->getBoard();
        }

        foreach ($similarPosts as $sPost) {
            if ($sPost->getBoard()->getIsActive()) {
                $allBoards[] = $sPost->getBoard();
            }
        }

        $post->setOtherBoards($allBoards);

        return $response;
    }

    /**
     * @REST\View
     */
    public function getSparkActivityAction(Request $request, $id)
    {
        $post = $this->getPostManager()
            ->getRepository()
            ->find($id);

        $response = new ApiResponse($post);
        if (!$post) {
            return $response->setError('Spark not found', Codes::HTTP_NOT_FOUND);
        } elseif ($post->getDeleted()) {
            return $response->setError('Spark has been removed', Codes::HTTP_GONE);
        }

        $qb = $this->getPostActivityManger()
                ->getRepository()
                ->createQueryBuilder()
                ->field('post')->references($post)
                ->field('isActive')->equals(true);

        $query = array(
            'limit' => $request->get('limit', 20),
            'page'  => $request->get('page', 1),
            'total' => $qb->count()->getQuery()->execute(),
        );

        $activity = $this->getPostActivityManger()
                ->getRepository()
                ->createQueryBuilder()
                ->field('post')->references($post)
                ->field('isActive')->equals(true)
                ->limit($query['limit'])
                ->skip(abs($query['page'] - 1) * $query['limit'])
                ->eagerCursor(true)
                ->getQuery()->execute();

        return new ApiResponse($activity, $query);
    }

    /**
     * @return \PW\PostBundle\Model\PostActivityManager
     */
    protected function getPostActivityManger()
    {
        return $this->container->get('pw_post.post_activity_manager');
    }

    /**
     * @return \PW\PostBundle\Model\PostManager
     */
    protected function getPostManager()
    {
        return $this->container->get('pw_post.post_manager');
    }
}
