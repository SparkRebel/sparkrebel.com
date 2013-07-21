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
class BrandController extends Controller
{
    /**
     * @REST\View
     */
    public function getBrandAction(Request $request, $id)
    {
        $user = $this->getUserManager()
            ->getRepository()
            ->find($id);

        $response = new ApiResponse($user);
        if (!$user) {
            return $response->setError('Brand not found', Codes::HTTP_NOT_FOUND);
        } elseif ($user->getDeleted()) {
            return $response->setError('Brand has been removed', Codes::HTTP_GONE);
        }

        return $response;
    }

    /**
     * @REST\View
     */
    public function getBrandsAction(Request $request)
    {
        $qb = $this->getUserManager()
            ->getRepository()
            ->findByType('brand')
            ->field('isActive')->equals(true);

        $query = array(
            'limit'  => $request->get('limit', 20),
            'page'   => $request->get('page', 1),
            'total'  => $qb->count()->getQuery()->execute(),
        );

        $users = $this->getUserManager()
            ->getRepository()
            ->findByType('brand')
            ->field('isActive')->equals(true)
            ->field('icon')->prime(true)
            ->limit($query['limit'])
            ->skip(abs($query['page'] - 1) * $query['limit'])
            ->eagerCursor(true)
            ->getQuery()->execute();

        return new ApiResponse($users, $query);
    }

    /**
     * @return \PW\UserBundle\Model\UserManager
     */
    protected function getUserManager()
    {
        return $this->container->get('pw_user.user_manager');
    }
}
