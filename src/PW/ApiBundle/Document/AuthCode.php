<?php

namespace PW\ApiBundle\Document;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use FOS\OAuthServerBundle\Model\AuthCode as BaseAuthCode;
use FOS\OAuthServerBundle\Model\ClientInterface;

/**
 * @MongoDB\Document(collection="api_auth_codes", repositoryClass="PW\ApiBundle\Repository\AuthCodeRepository")
 */
class AuthCode extends BaseAuthCode
{
    /**
     * @var \MongoId
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @var \PW\ApiBundle\Document\Client
     * @MongoDB\ReferenceOne(targetDocument="PW\ApiBundle\Document\Client", simple=true)
     */
    protected $client;

    /**
     * @var \PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User", simple=true)
     */
    protected $user;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $token;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $redirectUri;

    /**
     * @var int
     * @MongoDB\Timestamp
     */
    protected $expiresAt;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $scope;

    /**
     * Set client
     *
     * @param PW\ApiBundle\Document\Client $client
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Set user
     *
     * @param PW\UserBundle\Document\User $user
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    //
    // Doctrine Generation Below
    //

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get client
     *
     * @return PW\ApiBundle\Document\Client $client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get user
     *
     * @return PW\UserBundle\Document\User $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set token
     *
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * Get token
     *
     * @return string $token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set redirectUri
     *
     * @param string $redirectUri
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }

    /**
     * Get redirectUri
     *
     * @return string $redirectUri
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * Set expiresAt
     *
     * @param timestamp $expiresAt
     */
    public function setExpiresAt($expiresAt)
    {
        $this->expiresAt = $expiresAt;
    }

    /**
     * Get expiresAt
     *
     * @return timestamp $expiresAt
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * Set scope
     *
     * @param string $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * Get scope
     *
     * @return string $scope
     */
    public function getScope()
    {
        return $this->scope;
    }
}
