<?php

namespace PW\UserBundle\Controller\Api;

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
class UserController extends Controller
{
    /**
     * @REST\View
     */
    public function getUserAction(Request $request, $id)
    {
        $user = $this->getUserManager()
            ->getRepository()
            ->find($id);

        $response = new ApiResponse($user);
        if (!$user) {
            return $response->setError('User not found', Codes::HTTP_NOT_FOUND);
        } elseif ($user->getDeleted()) {
            return $response->setError('User has been removed', Codes::HTTP_GONE);
        }

        return $response;
    }

    /**
     * @REST\View
     */
    public function getUsersAction(Request $request)
    {
        $qb = $this->getUserManager()
            ->getRepository()
            ->findByType('user')
            ->field('isActive')->equals(true);

        $query = array(
            'limit'  => $request->get('limit', 20),
            'page'   => $request->get('page', 1),
            'total'  => $qb->count()->getQuery()->execute(),
        );

        $users = $this->getUserManager()
            ->getRepository()
            ->findByType('user')
            ->field('isActive')->equals(true)
            ->field('icon')->prime(true)
            ->limit($query['limit'])
            ->skip(abs($query['page'] - 1) * $query['limit'])
            ->eagerCursor(true)
            ->getQuery()->execute();

        return new ApiResponse($users, $query);
    }

    /**
     * @REST\View
     */
    public function getUserStreamAction(Request $request, $id)
    {
        $user = $this->getUserManager()
            ->getRepository()
            ->find($id);

        $response = new ApiResponse();
        if (!$user) {
            return $response->setError('User not found', Codes::HTTP_NOT_FOUND);
        } elseif ($user->getDeleted()) {
            return $response->setError('User has been removed', Codes::HTTP_GONE);
        }

        $redis = $this->get('snc_redis.default');
        $ids   = $redis->zrange('stream:{' . $user->getId() . '}:user', 0, 100);
        $query = array(
            'created_before' => $request->get('created_before', null),
            'limit' => $request->get('limit', 20),
            'type'  => $request->get('type', 'user'),
        );

        $posts = $this->getPostManager()
            ->getRepository()
            ->findLatest($query['created_before'])
            ->field('userType')->equals($query['type'])
            ->field('id')->in($ids)
            ->limit($query['limit'])
            ->eagerCursor(true)
            ->getQuery()->execute();

        if ($posts && $oldest = $posts->last() && $oldest->getCreated()) {
            $query['next'] = $this->generateUrl('api_get_channel_stream', array(
                'id' => $user->getId(),
                'created_before' => $oldest->getCreated()->getTimestamp(),
                'limit' => $query['limit'],
                'type' => $query['type'],
            ), true);
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
     * @return \PW\UserBundle\Model\UserManager
     */
    protected function getUserManager()
    {
        return $this->container->get('pw_user.user_manager');
    }
}
