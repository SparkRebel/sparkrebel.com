<?php

namespace PW\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    JMS\SecurityExtraBundle\Annotation\Secure;

class MerchantController extends Controller
{

    /**
     * View action
     *
     * @param string $id the id to view
     *
     * @Route("/store/{slug}.{_format}", defaults={"_format" = "html", "section" = "myBoards"}, requirements={"slug" = ".+", "_format" = "html|json"})
     * @Template("PWUserBundle:Brand:view.html.twig")
     * @return array
     */
    public function viewAction($slug, $section)
    {
        $me = $this->container->get('security.context')->getToken()->getUser();
        $dm = $this->container->get('doctrine_mongodb.odm.document_manager');

        $merchant = $dm->getRepository('PWUserBundle:Merchant')->findOneByUsername($slug);

        if (!$merchant) {
            throw $this->createNotFoundException("Merchant not found");
        } elseif ($merchant->getDeleted()) {
            throw $this->createNotFoundException("Merchant not found");
        }

        $automaticBoards = null;
        $curatedBoards   = null;

        $boardManager    = $this->container->get('pw_board.board_manager');
        $boardRepo       = $dm->getRepository('PWBoardBundle:Board');
        $automaticBoards = $boardRepo->findBy(
            array(
                'createdBy.$id'  => new \MongoId($merchant->getId()),
                'isSystem'       => true
            )
        );
        $automaticBoards = $boardManager->getCategorized($merchant);

        if (isset($automaticBoards['By Store'])) {
            $automaticBoards['By Brand'] = $automaticBoards['By Store'];
            unset($automaticBoards['By Store']);
        }

        $curatedBoards = $boardRepo->findBy(
            array(
                'createdBy.$id' => new \MongoId($merchant->getId()),
                'isSystem'      => false
            )
        );

        $isMe        = false;
        $isFollowing = false;
        if ($me instanceof \PW\UserBundle\Document\User) {
            /* @var $followManager \PW\UserBundle\Model\FollowManager */
            $followManager = $this->container->get('pw_user.follow_manager');
            $isFollowing   = $followManager->isFollowing($me, $merchant);
            $isMe          = ($merchant->getId() === $me->getId());
        }

        $followingUsers = $followingBrands = $followingVips   = array();
        $following = $followManager->getRepository()
            ->findFollowingByUser($merchant, 'users')
            ->getQuery()->execute();
        $followingCount = 0;
        foreach ($following as $follow) {
            $target = $follow->getTarget();
            $type   = $target->getUserType();
            if ($type === 'brand') {
                $followingBrands[] = $target;
            } elseif ($type === 'vip') {
                $followingVips[] = $target;
            } else {
                $followingUsers[] = $target;
            }
            $followingCount++;
        }

        $stream     = $this->get('pw.stream');
        $streamName = 'brand-' . $merchant->getId();
        $stream->resetStream($streamName);

        $posts = $dm->getRepository('PWPostBundle:Post')
            ->findLatest(array(), 2)
            ->field('createdBy')->references($merchant)
            ->getQuery()->execute()->toArray();
        if (!empty($posts) && ($oldest = end($posts)) && $oldest->getCreated()) {
            $stream->setNextTimeCriteria($streamName, $oldest->getCreated());
        }

        $followingBoards = $followManager->getRepository()
            ->findFollowingByUser($merchant, 'boards')
            ->getQuery()->execute();

        $followers = $followManager->getRepository()
            ->findFollowersByTarget($merchant)
            ->getQuery()->execute();

        $friends = null;
        if ($me && !$isMe) {
            $friends = $followManager->getMutualFriends($me, $merchant);
        }

        return array(
            'brand'           => $merchant,
            'isFollowing'     => $isFollowing,
            'isMe'            => $isMe,
            'followingCount'  => $followingCount,
            'followingBrands' => $followingBrands,
            'followingUsers'  => $followingUsers,
            'followingVips'   => $followingVips,
            'followingBoards' => $followingBoards,
            'friends'         => $friends,
            'followers'       => $followers,
            'boards'          => array(
                'automatic' => $automaticBoards,
                'curated'   => $curatedBoards,
                'following' => $followingBoards,
            ),
            'posts'     => $posts,
            'section'   => $section
        );
    }
}
