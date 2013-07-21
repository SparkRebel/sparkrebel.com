<?php

namespace PW\CategoryBundle\Controller\Api;

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
class CategoryController extends Controller
{
    /**
     * @REST\View
     */
    public function getChannelsStreamAction(Request $request)
    {
        return $this->handleStreamActions($request);
    }

    /**
     * @REST\View
     */
    public function getChannelStreamAction(Request $request, $id)
    {
        return $this->handleStreamActions($request, $id);
    }

    /**
     * @REST\View
     */
    public function getChannelsAction(Request $request)
    {
        $categories = $this->getCategoryManager()
            ->getRepository()
            ->findByType('user')
            ->field('parent')->prime(true)
            ->eagerCursor(true)
            ->getQuery()->execute();

        return new ApiResponse($categories);
    }

    /**
     * @REST\View
     */
    public function getChannelAction($id)
    {
        $category = $this->getCategoryManager()
            ->getRepository()
            ->find($id);

        $response = new ApiResponse($category);
        if (!$category) {
            return $response->setError('Channel not found', Codes::HTTP_NOT_FOUND);
        } elseif ($category->getDeleted()) {
            return $response->setError('Channel has been removed', Codes::HTTP_GONE);
        }

        return $response;
    }

    /**
     * @param Request $request
     * @param string $id
     * @return \PW\ApiBundle\Response\ApiResponse
     */
    protected function handleStreamActions(Request $request, $id = null)
    {
        if ($id !== null) {
            $category = $this->getCategoryManager()
                ->getRepository()
                ->find($id);

            $response = new ApiResponse($category);
            if (!$category) {
                return $response->setError('Channel not found', Codes::HTTP_NOT_FOUND);
            } elseif ($category->getDeleted()) {
                return $response->setError('Channel has been removed', Codes::HTTP_GONE);
            }
        }

        $query = array(
            'created_before' => $request->get('created_before', null),
            'limit' => $request->get('limit', 20),
        );

        $qb = $this->getPostManager()
            ->getRepository()
            ->findLatest($query['created_before'])
            ->field('userType')->equals('user');

        if ($id !== null) {
            $qb->field('category')->references($category);
        }

        $posts = $qb->eagerCursor(true)
            ->limit($query['limit'])
            ->getQuery()->execute();

        if ($posts && $oldest = $posts->last() && $oldest->getCreated()) {
            $queryParams = array(
                'created_before' => $oldest->getCreated()->getTimestamp(),
                'limit'          => $query['limit'],
            );
            if ($id !== null) {
                $queryParams['id'] = $category->getId();
                $query['next']     = $this->generateUrl('api_get_channel_stream', $queryParams, true);
            } else {
                $query['next'] = $this->generateUrl('api_get_channels_stream', $queryParams, true);
            }
        }

        return new ApiResponse($posts, $query);
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

    /**
     * @return \PW\CategoryBundle\Model\CategoryManager
     */
    protected function getCategoryManager()
    {
        return $this->container->get('pw_category.category_manager');
    }
}
