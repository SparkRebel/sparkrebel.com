<?php

namespace PW\UserBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\SoftDelete\SoftDeleteable;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Symfony\Component\Validator\Constraints as Assert;
use PW\ApplicationBundle\Document\AbstractDocument;
use JMS\SerializerBundle\Annotation as API;
use FOS\UserBundle\Model\User as BaseUser;
use PW\UserBundle\Document\User\Settings;
use PW\UserBundle\Document\User\Counts;
use PW\FlagBundle\Document\FlagSummary;

/**
 * @MongoDB\Document(collection="users", repositoryClass="PW\UserBundle\Repository\UserRepository")
 * @MongoDB\InheritanceType("SINGLE_COLLECTION")
 * @MongoDB\DiscriminatorField(fieldName="type")
 * @MongoDB\DiscriminatorMap({"user"="User", "brand"="Brand", "merchant"="Merchant"})
 * @MongoDB\Indexes({
 *      @MongoDB\UniqueIndex(keys={"slug"="asc"}, sparse=true, background=true),
 *      @MongoDB\UniqueIndex(keys={"usernameCanonical"="asc", "type"="asc"}, background=true),
 *      @MongoDB\UniqueIndex(keys={"name"="asc"}, background=true),
 *      @MongoDB\UniqueIndex(keys={"created"="asc"}, background=true),
 *      @MongoDB\UniqueIndex(keys={"email"="asc"}, background=true)
 * })
 * @MongoDBUnique(fields={"usernameCanonical"})
 * @API\ExclusionPolicy("none")
 * @API\AccessType("public_method")
 */
class User extends BaseUser implements SoftDeleteable
{
    /**
     * @var \MongoId
     * @MongoDB\Id
     * @API\Accessor(getter="getId", setter="setId")
     */
    protected $id;

    /**
     * @var string
     * @Gedmo\Slug(fields={"facebookId", "name"}, updatable=false)
     * @MongoDB\String
     */
    protected $slug;

    /**
     * @var string
     * @MongoDB\String
     * @API\Exclude
     */
    protected $subId;

    /**
     * @var array
     * @MongoDB\Hash
     * @API\Exclude
     */
    protected $utmData;

    /**
     * @var string
     * @MongoDB\String
     * @API\Exclude
     */
    protected $facebookId;

    /**
     * @var array
     * @MongoDB\Hash
     * @API\Exclude
     */
    protected $facebookData;

    /**
     * @var \PW\AssetBundle\Document\Asset
     * @MongoDB\ReferenceOne(targetDocument="PW\AssetBundle\Document\Asset")
     */
    protected $icon;

    /**
     * @var boolean
     * @MongoDB\Boolean
     * @API\Exclude
     */
    protected $isActive = true;

    /**
     * @var string
     * @MongoDB\String
     * @API\Exclude
     */
    protected $username;

    /**
     * @var string
     * @MongoDB\String
     * @API\SerializedName("username")
     */
    protected $usernameCanonical;

    /**
     * @var string
     * @MongoDB\String
     * @Assert\Email()
     * @API\Exclude
     */
    protected $email;

    /**
     * @var string
     * @MongoDB\String
     * @API\SerializedName("email")
     */
    protected $emailCanonical;

    /**
     * @var string
     * @MongoDB\String
     * @API\Exclude
     */
    protected $algorithm;

    /**
     * The salt to use for hashing
     *
     * @var string
     * @MongoDB\String
     * @API\Exclude
     */
    protected $salt;

    /**
     * Encrypted password. Must be persisted.
     *
     * @var string
     * @MongoDB\String
     * @API\Exclude
     */
    protected $password;

    /**
     * @var string
     * @MongoDB\String
     * @Assert\NotBlank(message="User Name cannot be left blank.")
     */
    protected $name;

    /**
     * @var array
     * @MongoDB\Field(type="PWDate")
     * @API\Exclude
     */
    protected $birthday;

    /**
     * @var string
     * @MongoDB\String
     * @API\Exclude
     */
    protected $about;

    /**
     * @var string
     * @MongoDB\String
     * @API\Exclude
     */
    protected $websiteUrl;

    /**
     * @var string
     * @MongoDB\String
     * @API\Exclude
     */
    protected $websiteTitle;

    /**
     * @var array
     * @MongoDB\Hash
     * @API\Exclude
     */
    protected $roles;

    /**
     * @var \PW\UserBundle\Document\User\Counts
     * @MongoDB\EmbedOne(targetDocument="PW\UserBundle\Document\User\Counts")
     */
    protected $counts;

    /**
     * @var \PW\UserBundle\Document\User\Settings
     * @MongoDB\EmbedOne(targetDocument="PW\UserBundle\Document\User\Settings")
     * @API\Exclude
     */
    protected $settings;

    /**
     * @var \PW\FlagBundle\Document\FlagSummary
     * @MongoDB\EmbedOne(targetDocument="PW\FlagBundle\Document\FlagSummary")
     * @API\Exclude
     */
    protected $flagSummary;

    /**
     * @var \PW\InviteBundle\Document\Code
     * @MongoDB\ReferenceOne(targetDocument="PW\InviteBundle\Document\Code", mappedBy="assignedUser", cascade={"persist"}, simple=true)
     * @MongoDB\NotSaved
     * @API\Exclude
     */
    protected $assignedInviteCode;

    /**
     * @var \PW\InviteBundle\Document\Code
     * @MongoDB\ReferenceOne(targetDocument="PW\InviteBundle\Document\Code", cascade={"persist"})
     * @MongoDB\NotSaved
     * @API\Exclude
     */
    protected $usedInviteCode;

    /**
     * @var \DateTime
     * @MongoDB\Date
     * @API\Exclude
     */
    protected $passwordRequestedAt;

    /**
     * @var \DateTime
     * @MongoDB\Date
     * @API\Exclude
     */
    protected $lastLogin;

    /**
     * @var int
     * @MongoDB\Int
     * @API\Exclude
     */
    protected $loginCount = 0;

    /**
     * @var string
     * @MongoDB\String
     * @API\Exclude
     */
    protected $confirmationToken;

    /**
     * @var boolean
     * @MongoDB\Boolean
     * @API\Exclude
     */
    protected $enabled;

    /**
     * @var string
     * @API\Exclude
     */
    protected $type;

    /**
     * @var \MongoDate
     * @MongoDB\Date
     * @Gedmo\Timestampable(on="create")
     * @API\Exclude
     */
    protected $created;

    /**
     * @var \MongoDate
     * @MongoDB\Date
     * @Gedmo\Timestampable(on="update")
     * @API\Exclude
     */
    protected $modified;

    /**
     * @var PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     * @API\Exclude
     */
    protected $modifiedBy;

    /**
     * @var \MongoDate
     * @MongoDB\Date
     * @API\Exclude
     */
    protected $deleted;

    /**
     * @var PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     * @API\Exclude
     */
    protected $deletedBy;

    /**
     * @MongoDB\ReferenceMany(targetDocument="PW\BoardBundle\Document\Board", mappedBy="createdBy", criteria={"isActive"=true}, sort={"name"="asc"})
     */
    protected $boards;

    /**
     * @var boolean
     * @MongoDB\Boolean
     * @API\Exclude
     */
    protected $disabledNotifications;


    /**
     * @var array
     * @MongoDB\Hash
     * @API\Exclude
     */
    protected $mailingsSent;

    public function __construct()
    {
        parent::__construct();
        $this->boards = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getBoards()
    {
        return $this->boards;
    }

    /**
     * getIsActive
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * setIsActive
     *
     * @param bool $isActive
     */
    public function setIsActive($isActive)
    {
        $this->enabled = $isActive;
        $this->isActive = $isActive;
    }

    /**
     * @return string
     */
    public function getUserType()
    {
        return 'user';
    }

    /**
     * Get settings
     *
     * @return PW\UserBundle\Document\User\Settings $settings
     */
    public function getSettings()
    {
        if (!$this->settings) {
            $this->settings = new Settings();
        }
        return $this->settings;
    }

    /**
     * Get counts
     *
     * @return PW\UserBundle\Document\User\Counts $counts
     */
    public function getCounts()
    {
        if (!$this->counts) {
             $this->counts = new Counts();
        }
        return $this->counts;
    }

    /**
     * Get flagSummary
     *
     * @return PW\FlagBundle\Document\FlagSummary $flagSummary
     */
    public function getFlagSummary()
    {
        if (!$this->flagSummary) {
            $this->flagSummary = new FlagSummary();
        }
        return $this->flagSummary;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->getUserType();
    }

    /**
     * Don't allow type setting
     *
     * @return void
     */
    public function setType()
    {
        return;
    }

    /**
     * increments login count
     */
    public function incLoginCount()
    {
        return ++$this->loginCount;
    }

    /**
     * Sets the email.
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        parent::setEmail($email);
        $this->setUsername($email);
    }

    /**
     * Set facebookData
     *
     * @param hash $facebookData
     */
    public function setFacebookData($facebookData)
    {
        $this->facebookData = $facebookData;

        $name = false;
        if (!empty($facebookData['name'])) {
            $name = $facebookData['name'];
        } else {
            if (!empty($facebookData['first_name'])) {
                $name = $facebookData['first_name'];
                if (!empty($facebookData['last_name'])) {
                    $name .= trim(' ' . $facebookData['last_name']);
                }
            } else {
                if (!empty($facebookData['username'])) {
                    $name = $facebookData['username'];
                }
            }
        }

        if (empty($name)) {
            $name = 'Anonymous';
        }
        $this->setName($name);

        if (isset($facebookData['email'])) {
            $this->setEmail($facebookData['email']);
        }
    }

    /**
     * Sets the last login time
     *
     * @param \DateTime $time
     */
    public function setLastLogin(\DateTime $time)
    {
        return parent::setLastLogin($time);
    }

    /**
     * Sets the timestamp that the user requested a password reset.
     *
     * @param \DateTime $date
     */
    public function setPasswordRequestedAt(\DateTime $date = null)
    {
        return parent::setPasswordRequestedAt($date);
    }

    /**
     * Sets the roles of the user.
     *
     * This overwrites any previous roles.
     *
     * @param array $roles
     */
    public function setRoles(array $roles)
    {
        return parent::setRoles($roles);
    }

    /**
     * Get roles
     *
     * @return hash $roles
     */
    public function getRoles()
    {
        return parent::getRoles();
    }

    /**
     * @return array
     */
    public function getAdminData()
    {
        $data = AbstractDocument::staticGetAdminData($this);
        $data['display'] = $this->getName();

        if ($assignedInviteCode = $this->getAssignedInviteCode()) {
            if (is_object($assignedInviteCode) && $assignedInviteCode instanceOf \PW\InviteBundle\Document\Code) {
                $data['assignedInviteCode'] = $assignedInviteCode->getAdminData();
            }
        }

        if ($usedInviteCode = $this->getUsedInviteCode()) {
            if (is_object($usedInviteCode) && $usedInviteCode instanceOf \PW\InviteBundle\Document\Code) {
                $data['usedInviteCode'] = $usedInviteCode->getAdminData();
            }
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getAdminValue()
    {
        return $this->getName();
    }

    /**
     * @param array $data
     * @param mixed $object
     * @return \PW\UserBundle\Document\User
     */
    public function fromArray(array $data = array())
    {
        return AbstractDocument::staticFromArray($this, $data);
    }

    /**
     * @param mixed $object
     * @return array
     */
    public function toArray()
    {
        return AbstractDocument::staticToArray($this);
    }

    /**
     * Serialize data we want available in APP.me
     * "safe" because it is visible from JavaScript
     *
     * @return array
     */
    public function safeToArray()
    {
        return array(
            'id'       => $this->getId(),
            'name'     => $this->getName(),
            'counts'   => $this->getCounts()->toArray(),
            'settings' => $this->getSettings()->safeToArray(),
            'hasCuratorRole' => $this->hasRole('ROLE_CURATOR'),
        );
    }

    /**
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->getDeleted();
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
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get slug
     *
     * @return string $slug
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set facebookId
     *
     * @param string $facebookId
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;
    }

    /**
     * Get facebookId
     *
     * @return string $facebookId
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * Get facebookData
     *
     * @return hash $facebookData
     */
    public function getFacebookData()
    {
        return $this->facebookData;
    }

    /**
     * Set icon
     *
     * @param PW\AssetBundle\Document\Asset $icon
     */
    public function setIcon(\PW\AssetBundle\Document\Asset $icon)
    {
        $this->icon = $icon;
    }

    /**
     * Get icon
     *
     * @return PW\AssetBundle\Document\Asset $icon
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set username
     *
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Get username
     *
     * @return string $username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set usernameCanonical
     *
     * @param string $usernameCanonical
     */
    public function setUsernameCanonical($usernameCanonical)
    {
        $this->usernameCanonical = $usernameCanonical;
    }

    /**
     * Get usernameCanonical
     *
     * @return string $usernameCanonical
     */
    public function getUsernameCanonical()
    {
        return $this->usernameCanonical;
    }

    /**
     * Get email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set emailCanonical
     *
     * @param string $emailCanonical
     */
    public function setEmailCanonical($emailCanonical)
    {
        $this->emailCanonical = $emailCanonical;
    }

    /**
     * Get emailCanonical
     *
     * @return string $emailCanonical
     */
    public function getEmailCanonical()
    {
        return $this->emailCanonical;
    }

    /**
     * Set algorithm
     *
     * @param string $algorithm
     */
    public function setAlgorithm($algorithm)
    {
        $this->algorithm = $algorithm;
    }

    /**
     * Get algorithm
     *
     * @return string $algorithm
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    /**
     * Set salt
     *
     * @param string $salt
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    /**
     * Get salt
     *
     * @return string $salt
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Get password
     *
     * @return string $password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set birthday
     *
     * @param PWDate $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    /**
     * Get birthday
     *
     * @return PWDate $birthday
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set about
     *
     * @param string $about
     */
    public function setAbout($about)
    {
        $this->about = $about;
    }

    /**
     * Get about
     *
     * @return string $about
     */
    public function getAbout()
    {
        return $this->about;
    }

    /**
     * Set websiteUrl
     *
     * @param string $websiteUrl
     */
    public function setWebsiteUrl($websiteUrl)
    {
        $this->websiteUrl = $websiteUrl;
    }

    /**
     * Get websiteUrl
     *
     * @return string $websiteUrl
     */
    public function getWebsiteUrl()
    {
        return $this->websiteUrl;
    }

    /**
     * Set websiteTitle
     *
     * @param string $websiteTitle
     */
    public function setWebsiteTitle($websiteTitle)
    {
        $this->websiteTitle = $websiteTitle;
    }

    /**
     * Get websiteTitle
     *
     * @return string $websiteTitle
     */
    public function getWebsiteTitle()
    {
        return $this->websiteTitle;
    }

    /**
     * Set counts
     *
     * @param PW\UserBundle\Document\User\Counts $counts
     */
    public function setCounts(\PW\UserBundle\Document\User\Counts $counts)
    {
        $this->counts = $counts;
    }

    /**
     * Set settings
     *
     * @param PW\UserBundle\Document\User\Settings $settings
     */
    public function setSettings(\PW\UserBundle\Document\User\Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Set flagSummary
     *
     * @param PW\FlagBundle\Document\FlagSummary $flagSummary
     */
    public function setFlagSummary(\PW\FlagBundle\Document\FlagSummary $flagSummary)
    {
        $this->flagSummary = $flagSummary;
    }

    /**
     * Set assignedInviteCode
     *
     * @param PW\InviteBundle\Document\Code $assignedInviteCode
     */
    public function setAssignedInviteCode(\PW\InviteBundle\Document\Code $assignedInviteCode)
    {
        $this->assignedInviteCode = $assignedInviteCode;
    }

    /**
     * Get assignedInviteCode
     *
     * @return PW\InviteBundle\Document\Code $assignedInviteCode
     */
    public function getAssignedInviteCode()
    {
        return $this->assignedInviteCode;
    }

    /**
     * Set usedInviteCode
     *
     * @param PW\InviteBundle\Document\Code $usedInviteCode
     */
    public function setUsedInviteCode(\PW\InviteBundle\Document\Code $usedInviteCode)
    {
        $this->usedInviteCode = $usedInviteCode;
    }

    /**
     * Get usedInviteCode
     *
     * @return PW\InviteBundle\Document\Code $usedInviteCode
     */
    public function getUsedInviteCode()
    {
        return $this->usedInviteCode;
    }

    /**
     * Get passwordRequestedAt
     *
     * @return date $passwordRequestedAt
     */
    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    /**
     * Get lastLogin
     *
     * @return date $lastLogin
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Set loginCount
     *
     * @param increment $loginCount
     */
    public function setLoginCount($loginCount)
    {
        $this->loginCount = $loginCount;
    }

    /**
     * Get loginCount
     *
     * @return increment $loginCount
     */
    public function getLoginCount()
    {
        return $this->loginCount;
    }

    /**
     * Set confirmationToken
     *
     * @param string $confirmationToken
     */
    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;
    }

    /**
     * Get confirmationToken
     *
     * @return string $confirmationToken
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * Get enabled
     *
     * @return boolean $enabled
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set created
     *
     * @param date $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * Get created
     *
     * @return date $created
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set modified
     *
     * @param date $modified
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
    }

    /**
     * Get modified
     *
     * @return date $modified
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set modifiedBy
     *
     * @param PW\UserBundle\Document\User $modifiedBy
     */
    public function setModifiedBy(\PW\UserBundle\Document\User $modifiedBy)
    {
        $this->modifiedBy = $modifiedBy;
    }

    /**
     * Get modifiedBy
     *
     * @return PW\UserBundle\Document\User $modifiedBy
     */
    public function getModifiedBy()
    {
        return $this->modifiedBy;
    }

    /**
     * Set deleted
     *
     * @param date $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * Get deleted
     *
     * @return date $deleted
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set deletedBy
     *
     * @param PW\UserBundle\Document\User $deletedBy
     */
    public function setDeletedBy(\PW\UserBundle\Document\User $deletedBy = null)
    {
        $this->deletedBy = $deletedBy;
    }

    /**
     * Get deletedBy
     *
     * @return PW\UserBundle\Document\User $deletedBy
     */
    public function getDeletedBy()
    {
        return $this->deletedBy;
    }

    /**
     * Set subId
     *
     * @param string $subId
     */
    public function setSubId($subId)
    {
        $this->subId = $subId;
    }

    /**
     * Get subId
     *
     * @return string $subId
     */
    public function getSubId()
    {
        return $this->subId;
    }

    /**
     * Set utmData
     *
     * @param hash $utmData
     */
    public function setUtmData($utmData)
    {
        $this->utmData = $utmData;
    }

    /**
     * Get utmData
     *
     * @return hash $utmData
     */
    public function getUtmData()
    {
        return $this->utmData;
    }

    /**
     * Add boards
     *
     * @param PW\BoardBundle\Document\Board $boards
     */
    public function addBoards(\PW\BoardBundle\Document\Board $boards)
    {
        $this->boards[] = $boards;
    }

    /**
     * returns user first name if avilable
     *
     * @return string
     */
    public function getFirstName()
    {
        if(isset($this->facebookData['first_name'])) {
            return $this->facebookData['first_name'];
        } else {
            return $this->getName();
        }
    }

    /**
     * @return bool
     */
    public function isCeleb()
    {
        return $this->getName() === 'Celebs';
    }

    /**
     * @return boolean
     */
    public function hasDisabledNotifications()
    {
        return $this->disabledNotifications === true;
    }

    /**
     * @param bool $value
     * @return PW\UserBundle\Document\User
     */
    public function setDisabledNotifications($value)
    {
        $this->disabledNotifications = (bool) $value;
        return $this;
    }

    /**
     * Get disabledNotifications
     *
     * @return boolean $disabledNotifications
     */
    public function getDisabledNotifications()
    {
        return $this->disabledNotifications;
    }

    
    public function getMailingsSent()
    {
        return $this->mailingsSent;
    }
    
    public function setMailingsSent($value)
    {
        $this->mailingsSent = $value;    
        return $this;
    }

    /**
     * Updates mailings sent key, so we know when mailing was sent for user
     *     
     **/    
    public function updateMailing($key)
    {

        $dt = new \DateTime;
        $this->mailingsSent[$key] = new \MongoDate($dt->getTimestamp());
    }
}
