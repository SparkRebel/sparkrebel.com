<?php

namespace PW\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use JMS\SecurityExtraBundle\Annotation\Secure;
use PW\ApplicationBundle\Response\JsonResponse;

/**
 * Notification Controller
 */
class NotificationController extends Controller
{
    /**
     * List all notifications for current User
     *
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Route("/notifications/list/{id}/{startTs}", defaults={"startTs"=null}, requirements={"startTs"="\d+"}, name="activity_notification_list")
     * @Template
     * @Cache(maxage="30")
     */
    public function listAction(Request $request, $id, $startTs = null)
    {
        // Don't allow Users to get notifications for others
        $security = $this->get('security.context');
        $me       = $security->getToken()->getUser();
        if ($me->getId() != $id) {
            if (!$security->isGranted('ROLE_PREVIOUS_ADMIN') && !$security->isGranted('ROLE_ADMIN')) {
                return new Response(null, 403);
            }
        }

        $pageSize = 50;
        $startDate = new \DateTime('tomorrow - 1 second');
        if (!empty($startTs)) {
            $startDate = \DateTime::createFromFormat('U', $startTs);
        }

        /* @var $notificationManager \PW\ActivityBundle\Model\NotificationManager */
        $notificationManager = $this->get('pw_activity.notification_manager');

        $count = $notificationManager->getRepository()
            ->findByUser($me)
            ->field('created')->lt($startDate)
            ->count()->getQuery()->execute();

        $notifications = $notificationManager->getRepository()
            ->findByUser($me)
            ->eagerCursor(true)
            ->field('created')->lt($startDate)
            ->limit($pageSize)
            ->getQuery()->execute();

        return array(
            'notifications' => $notifications,
            'startTs'       => $startTs,
            'more'          => ($count > $pageSize),
            'id'            => $id,
            'title'         => 'Notifications',
            'path'          => 'activity_notification_list',
        );
    }

    /**
     * Get last 25 notifications for a User
     *
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Route("/notifications/user/{id}.{_format}", defaults={"_format"="html"}, requirements={"_format"="html|json"}, name="activity_notification_index")
     * @Template
     */
    public function indexAction(Request $request, $id)
    {
        // Don't allow Users to get notifications for others
        $security = $this->get('security.context');
        $me       = $security->getToken()->getUser();
        if ($me->getId() != $id) {
            if (!$security->isGranted('ROLE_PREVIOUS_ADMIN') && !$security->isGranted('ROLE_ADMIN')) {
                return new Response(null, 403);
            }
        }

        /* @var $notificationManager \PW\ActivityBundle\Model\NotificationManager */
        $notificationManager = $this->get('pw_activity.notification_manager');

        $response = new Response();
        if ($last = $notificationManager->getRepository()->findByUser($me)->getQuery()->getSingleResult()) {
            $response->setLastModified($last->getLastModified());
            if ($response->isNotModified($request)) {
                return $response;
            }
        }

        $newCount = $notificationManager->getRepository()
            ->findByUser($me, array('onlyNew' => true))
            ->count()->getQuery()->execute();

        $qb = $notificationManager->getRepository()
            ->findByUser($me);

        $result = array(
            'notifications' => $this->get('knp_paginator')->paginate(
                $qb,
                $request->query->get('page', 1),
                $request->query->get('pagesize', 25)
            ),
            'newCount'     => $newCount,
            'lastModified' => $response->getLastModified(),
        );

        if ($request->get('_format') == 'json') {
            return new JsonResponse($result, $response);
        } else {
            return $result;
        }
    }

    /**
     * Mark all User's notifications as read
     *
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Route("/notifications/notifications/markasread/{id}/{ts}", requirements={"ts"="\d+"})
     * @Template
     */
    public function markAsReadAction($id, $ts)
    {
        // Don't allow Users to markAsRead notifications for others
        $security = $this->get('security.context');
        $me       = $security->getToken()->getUser();
        if ($me->getId() != $id) {
            if (!$security->isGranted('ROLE_PREVIOUS_ADMIN') && !$security->isGranted('ROLE_ADMIN')) {
                return new Response(null, 403);
            }
        }

        /* @var $notificationManager \PW\ActivityBundle\Model\NotificationManager */
        $notificationManager = $this->get('pw_activity.notification_manager');

        $changed = $notificationManager->getRepository()
			->createQueryBuilder()
            ->update()->multiple(true)
            ->field('isNew')->set(false)->equals(true)
            ->field('modified')->set(new \MongoDate())
            ->field('user')->references($me)
            ->field('category')->equals('user')
            ->field('created')->lte(new \MongoDate($ts))
            ->getQuery()->execute();

        return new JsonResponse(array('result' => $changed));
    }
}
