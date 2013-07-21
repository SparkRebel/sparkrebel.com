<?php

namespace PW\BoardBundle\Controller\Api;

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
class BoardController extends Controller
{
    /**
     * @REST\View
     */
    public function getBoardsAction(Request $request)
    {
        $qb = $this->getBoardManager()
            ->getRepository()
            ->createQueryBuilder()
            ->field('isActive')->equals(true);

        $query = array(
            'limit'  => $request->get('limit', 20),
            'page'   => $request->get('page', 1),
            'total'  => $qb->count()->getQuery()->execute(),
        );

        $boards = $this->getBoardManager()
            ->getRepository()
            ->createQueryBuilder()
            ->field('isActive')->equals(true)
            ->field('category')->prime(true)
            ->field('createdBy')->prime(true)
            ->limit($query['limit'])
            ->skip(abs($query['page'] - 1) * $query['limit'])
            ->eagerCursor(true)
            ->getQuery()->execute();

        return new ApiResponse($boards, $query);
    }

    /**
     * @REST\View
     */
    public function getBoardAction($id)
    {
        $board = $this->getBoardManager()
            ->getRepository()
            ->find($id);

        $response = new ApiResponse();
        if (!$board) {
            return $response->setError('Board not found', Codes::HTTP_NOT_FOUND);
        } elseif ($board->getDeleted()) {
            return $response->setError('Board has been removed', Codes::HTTP_GONE);
        }

        $posts = $this->getPostManager()
            ->getRepository()
            ->findByBoard($board)
            ->eagerCursor(true)
            ->getQuery()->execute();

        $board->setPosts($posts);
        $response->setResult($board);

        return $response;
    }

    /**
     * @REST\View
     */
    public function getBoardStreamAction(Request $request, $id)
    {
        $board = $this->getBoardManager()
            ->getRepository()
            ->find($id);

        $response = new ApiResponse();
        if (!$board) {
            return $response->setError('Board not found', Codes::HTTP_NOT_FOUND);
        } elseif ($board->getDeleted()) {
            return $response->setError('Board has been removed', Codes::HTTP_GONE);
        }

        $query = array(
            'created_before' => $request->get('created_before', null),
            'limit' => $request->get('limit', 20),
            'type'  => $request->get('type', 'user'),
        );

        $posts = $this->getPostManager()
            ->getRepository()
            ->findLatest($query['created_before'])
            ->field('board')->references($board)
            ->field('userType')->equals($query['type'])
            ->limit($query['limit'])
            ->eagerCursor(true)
            ->getQuery()->execute();

        if ($posts && $oldest = $posts->last() && $oldest->getCreated()) {
            $query['next'] = $this->generateUrl('api_get_channel_stream', array(
                'id' => $board->getId(),
                'created_before' => $oldest->getCreated()->getTimestamp(),
                'limit' => $query['limit'],
                'type' => $query['type'],
            ), true);
        }

        return new ApiResponse($posts, $query);
    }

    /**
     * @REST\View
     */
    public function postBoardsAction(Request $request)
    {
        /* @var $boardManager  */
        $boardManager = $this->get('');
        $board = $boardManager->create();

        if ($request->get('category', false)) {
            /* @var $categoryManager \PW\CategoryBundle\Model\CategoryManager */
            $categoryManager = $this->get('pw_category.category_manager');
            $category = $categoryManager->find($request->get('category'));
            if (!$category) {
                throw $this->createNotFoundException("Channel not found");
            } elseif ($category->getDeleted()) {
                throw $this->createNotFoundException("Channel has been removed");
            }
            $board->setCategory($category);
        }

        $board->setName($request->get('name'));

        $validator = $this->get('validator');
        $errors = $validator->validate($board);

        if (count($errors) > 0) {
            throw new HttpException(400, "Board is invalid.");
        } else {
            return array(
                'board' => $board,
            );
        }
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
     * @return \PW\BoardBundle\Model\BoardManager
     */
    protected function getBoardManager()
    {
        return $this->container->get('pw_board.board_manager');
    }
}
