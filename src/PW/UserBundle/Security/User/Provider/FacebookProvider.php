<?php

namespace PW\UserBundle\Security\User\Provider;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use \BaseFacebook;
use \FacebookApiException;
use PW\UserBundle\Document\User;

class FacebookProvider implements UserProviderInterface
{
    /**
     * @var \Facebook
     */
    protected $facebook;

    /**
     * @var \PW\UserBundle\Model\UserManager
     */
    protected $userManager;

    /**
     * @var mixed
     */
    protected $validator;

    /**
     * @var mixed
     */
    protected $session;

    /**
     * @var \PW\ApplicationBundle\Model\EventManager
     */
    protected $eventManager;

    /**
     * @param BaseFacebook $facebook
     * @param type $userManager
     * @param type $validator
     */
    public function __construct(BaseFacebook $facebook, $userManager, $validator)
    {
        $this->facebook    = $facebook;
        $this->userManager = $userManager;
        $this->validator   = $validator;
    }

    public function supportsClass($class)
    {
        return $this->userManager->supportsClass($class);
    }

    public function findUserByFacebookId($fbId)
    {
        return $this->userManager->findUserBy(array('facebookId' => $fbId));
    }

    /**
     * If we got to this provider, username is actually the fbId
     *
     * @param int $fbId
     */
    public function loadUserByUsername($fbId)
    {
        $user = $this->findUserByFacebookId($fbId);

        try {
            $fbData = $this->facebook->api('/'.$fbId);
        } catch (FacebookApiException $e) {
            $fbData = null;
        }

        // Seems fb api can return an error_code upon error without raising an exception
        if (isset($fbData['error_code']) && $fbData['error_code'] != 0) {
            $fbData = null;
        }

        if (!empty($fbData)) {
            $userExisted = true;
            if (empty($user)) {
                $userExisted = false;
                $user = $this->userManager->createUser();
                $user->setEnabled(true);
                $user->setPassword('');
            }

            // TODO use http://developers.facebook.com/docs/api/realtime
            $user->setFacebookId($fbData['id']);
            $user->setFacebookData($fbData);

            if (count($this->validator->validate($user, 'Facebook'))) {
                // TODO: the user was found obviously, but doesnt match our expectations, do something smart
                throw new UsernameNotFoundException('The facebook user could not be stored');
            }

            $this->userManager->updateUser($user);

            $this->eventManager->requestJob('user:icon:refresh ' . $user->getId());
            if (!$userExisted) {
                $this->userManager->getMailer()->sendWelcomeEmailMessage($user);
                $this->eventManager->requestJob('follow:fbfriends ' . $user->getId());
            }
        }

        if (empty($user)) {
            throw new UsernameNotFoundException('The user is not authenticated on facebook');
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user)) || !$user->getFacebookId()) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getFacebookId());
    }

    /**
     * @param \PW\UserBundle\Document\User $user
     * @param string $next
     * @return array
     */
    public function getInstalledFriends(User $user, $next = null)
    {
        $result = array('users' => array(), 'next' => false);

        if (!$user->getFacebookId()) {
            return $result;
        }

        $url = sprintf("/%s/friends?fields=installed", $user->getFacebookId());
        if (!empty($next)) {
            $url = str_replace(BaseFacebook::$DOMAIN_MAP['graph'], '', $next);
        }

        $data = $this->facebook->api($url);

        if (!empty($data['data'])) {
            $result['users'] = $data['data'];
        }

        if (!empty($data['paging']['next'])) {
            $result['next'] = $data['paging']['next'];
        }

        return $result;
    }

    /**
     * @param mixed $session
     */
    public function setSession($session = null)
    {
        $this->session = $session;
    }

    /**
     * @param \PW\ApplicationBundle\Model\EventManager $eventManager
     */
    public function setEventManager(\PW\ApplicationBundle\Model\EventManager $eventManager = null)
    {
        $this->eventManager = $eventManager;
    }
}
