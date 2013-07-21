<?php

/**
 * @author Radu Topala <radu@sparkrebel.com>
 */

namespace PW\NewsletterBundle\Document;

use PW\ApplicationBundle\Document\AbstractDocument,
    Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB,
    Symfony\Component\Validator\Constraints as Assert,
    Gedmo\Mapping\Annotation as Gedmo,
    PW\AssetBundle\Document\Asset;

/**
 * Newsletter
 *
 * @MongoDB\Document(collection="newsletters", repositoryClass="PW\NewsletterBundle\Repository\NewsletterRepository")
 */
class Newsletter extends AbstractDocument
{
    /**
    * @MongoDB\Id
    */
    protected $id;

    /**
     * @var string
     * @MongoDB\String
     * @Assert\NotBlank(message="Newsletter Subject cannot be left blank.")
     */
    protected $subject = 'What\'s this week’s MUST HAVE, Fashion No No\'s and what she was wearing last night...Find out now at SparkRebel.com!';
    
    /**
     * @MongoDB\String
     */
    protected $heading;
    
    /**
     * @var string
     * @Gedmo\Slug(fields={"subject"})
     * @MongoDB\String
     */
    protected $slug;

    /**
     * @MongoDB\String
     * @Assert\Choice(choices = {"review", "pending", "sent"}, message = "Choose a valid Newsletter status.")
     */
    protected $status = 'review';
    
    /**
     * @MongoDB\String
     * @Assert\Choice(choices = {"n/a", "curated", "brands", "celebs", "events"}, message = "Choose a valid Newsletter top section type.")
     */
    protected $topType = 'n/a';

    /**
     * @MongoDB\String
     */
    protected $curatedTopTitle;

    /**
     * @var \PW\AssetBundle\Document\Asset
     * @MongoDB\ReferenceOne(targetDocument="PW\AssetBundle\Document\Asset")
     */
    protected $curatedTopImage;

    /**
     * @MongoDB\String
     */
    protected $curatedTopLink;

    /**
     * @MongoDB\String
     */
    protected $curatedTopDescription;

    /**
     * @MongoDB\String
     */
    protected $curatedTopContent;

    /**
     * @MongoDB\ReferenceOne(targetDocument="PW\BoardBundle\Document\Board")
     */
    protected $eventsTopBoard;

    /**
     * @MongoDB\String
     * @Assert\Choice(choices = {"n/a", "curated", "brands", "celebs", "events"}, message = "Choose a valid Newsletter bottom section type.")
     */
    protected $bottomType = 'n/a';

    /**
     * @MongoDB\String
     */
    protected $curatedBottomTitle;

    /**
     * @var \PW\AssetBundle\Document\Asset
     * @MongoDB\ReferenceOne(targetDocument="PW\AssetBundle\Document\Asset")
     */
    protected $curatedBottomImage;

    /**
     * @MongoDB\String
     */
    protected $curatedBottomLink;

    /**
     * @MongoDB\String
     */
    protected $curatedBottomDescription;

    /**
     * @MongoDB\String
     */
    protected $curatedBottomContent;

    /**
     * @MongoDB\ReferenceOne(targetDocument="PW\BoardBundle\Document\Board")
     */
    protected $eventsBottomBoard;

    /**
     * @MongoDB\Boolean
     */
    protected $showFromYourFavoriteBrands;
    
    /**
     * @MongoDB\Boolean
     */
    protected $showFromYourStream;
    
    /**
     * @MongoDB\Boolean
     */
    protected $showTrendingCollections;

    /**
     * @Gedmo\Timestampable(on="create")
     * @MongoDB\Date
     */
    protected $created;

    /**
     * @Gedmo\Timestampable(on="update")
     * @MongoDB\Date
     */
    protected $modified;

    /**
     * @MongoDB\Date
     */
    protected $sendAt;

    /**
     * @MongoDB\Date
     */
    protected $sentAt;

    /**
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     */
    protected $createdBy;

    /**
     * @var int
     * @MongoDB\Int
     */
    protected $sentTo = 0;

    /**
     * parse curated content
     *
     * @param $content
     * @param $hrefAbsolute
     * @param $tracking
     * @return string
     */
    protected function parseCuratedContent($content, $hrefAbsolute, $tracking)
    {
        $hrefAbsolute = rtrim($hrefAbsolute,'/');
        $hrefColorStyle = 'color: #ed2978;';
        $hrefRelative = array('../../../..', '../../..', '../..');

        $dom = new \DOMDocument;
        $dom->loadHTML($content);
        foreach ($dom->getElementsByTagName('a') as $node) {
            if($node->hasAttribute('style')) {
                $node->setAttribute('style', $hrefColorStyle.$node->getAttribute('style'));
            }
            else {
                $node->setAttribute('style', $hrefColorStyle);
            }

            $node->setAttribute('href', str_replace($hrefRelative, $hrefAbsolute, $node->getAttribute('href')));

            if(substr_count($node->getAttribute('href'), $hrefAbsolute) > 0) {
                $node->setAttribute('href', $node->getAttribute('href').'?'.$tracking);
            }
        }
        $content = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace( array('<html>', '</html>', '<body>', '</body>'), array(''), $dom->saveHTML()));

        return $content;
    }

    /**
     * make some html/css parsing for top content to look better in the email
     *
     * @return string
     */
    public function getCuratedTopContentParsed($hrefAbsolute = 'http://sparkrebel.com', $tracking = '')
    {
        return $this->parseCuratedContent($this->getCuratedTopContent(), $hrefAbsolute, $tracking);
    }

    /**
     * make some html/css parsing for bottom content to look better in the email
     *
     * @return string
     */
    public function getCuratedBottomContentParsed($hrefAbsolute = 'http://sparkrebel.com', $tracking = '')
    {
        return $this->parseCuratedContent($this->getCuratedBottomContent(), $hrefAbsolute, $tracking);
    }

    /**
     * parse curated link
     *
     * @param $link
     * @param $hrefAbsolute
     * @param $tracking
     * @return string
     */
    protected function parseCuratedLink($link, $hrefAbsolute, $tracking)
    {
        $hrefAbsolute = rtrim($hrefAbsolute,'/');
        $hrefRelative = array('../../../..', '../../..', '../..');

        $link = str_replace($hrefRelative, $hrefAbsolute, $link);

        if(substr_count($link, $hrefAbsolute) > 0) {
            $link.='?'.$tracking;
        }

        return $link;
    }

    /**
     * make some html/css parsing for top link to look better in the email
     *
     * @return string
     */
    public function getCuratedTopLinkParsed($hrefAbsolute = 'http://sparkrebel.com', $tracking = '')
    {
        return $this->parseCuratedLink($this->getCuratedTopLink(), $hrefAbsolute, $tracking);
    }

    /**
     * make some html/css parsing for bottom link to look better in the email
     *
     * @return string
     */
    public function getCuratedBottomLinkParsed($hrefAbsolute = 'http://sparkrebel.com', $tracking = '')
    {
        return $this->parseCuratedLink($this->getCuratedBottomLink(), $hrefAbsolute, $tracking);
    }

    /**
     * Symfony autogenerated below
     */

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
     * Set subject
     *
     * @param string $subject
     * @return Newsletter
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Get subject
     *
     * @return string $subject
     */
    public function getSubject()
    {
        return $this->subject;
    }
    
    /**
     * Set heading (text under: Hi username)
     *
     * @param string $heading
     * @return Newsletter
     */
    public function setHeading($heading)
    {
        $this->heading = $heading;
        return $this;
    }

    /**
     * Get heading
     *
     * @return string $heading
     */
    public function getHeading()
    {
        return $this->heading;
    }
    
    /**
     * Set created
     *
     * @param date $created
     * @return Newsletter
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
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
     * @return Newsletter
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
        return $this;
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
     * Set sendAt
     *
     * @param date $sendAt
     * @return Newsletter
     */
    public function setSendAt($sendAt)
    {
        $this->sendAt = $sendAt;
        return $this;
    }

    /**
     * Get sendAt
     *
     * @return date $sendAt
     */
    public function getSendAt()
    {
        return $this->sendAt;
    }

    /**
     * Set sentAt
     *
     * @param date $sentAt
     * @return Newsletter
     */
    public function setSentAt($sentAt)
    {
        $this->sentAt = $sentAt;
        return $this;
    }

    /**
     * Get sentAt
     *
     * @return date $sentAt
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    /**
     * Set createdBy
     *
     * @param PW\UserBundle\Document\User $createdBy
     * @return Newsletter
     */
    public function setCreatedBy(\PW\UserBundle\Document\User $createdBy)
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    /**
     * Get createdBy
     *
     * @return PW\UserBundle\Document\User $createdBy
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Newsletter
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Newsletter
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
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
     * Set topType
     *
     * @param string $topType
     * @return Newsletter
     */
    public function setTopType($topType)
    {
        $this->topType = $topType;
        return $this;
    }

    /**
     * Get topType
     *
     * @return string $topType
     */
    public function getTopType()
    {
        return $this->topType;
    }

    /**
     * Set bottomType
     *
     * @param string $bottomType
     * @return Newsletter
     */
    public function setBottomType($bottomType)
    {
        $this->bottomType = $bottomType;
        return $this;
    }

    /**
     * Get bottomType
     *
     * @return string $bottomType
     */
    public function getBottomType()
    {
        return $this->bottomType;
    }
    /**
     * @var boolean $isActive
     */
    protected $isActive;

    /**
     * @var date $deleted
     */
    protected $deleted;


    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return Newsletter
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean $isActive
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set deleted
     *
     * @param date $deleted
     * @return Newsletter
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
        return $this;
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
     * Set eventsTopBoard
     *
     * @param PW\BoardBundle\Document\Board $eventsTopBoard
     * @return Newsletter
     */
    public function setEventsTopBoard(\PW\BoardBundle\Document\Board $eventsTopBoard)
    {
        $this->eventsTopBoard = $eventsTopBoard;
        return $this;
    }

    /**
     * Get eventsTopBoard
     *
     * @return PW\BoardBundle\Document\Board $eventsTopBoard
     */
    public function getEventsTopBoard()
    {
        return $this->eventsTopBoard;
    }

    /**
     * Set eventsBottomBoard
     *
     * @param PW\BoardBundle\Document\Board $eventsBottomBoard
     * @return Newsletter
     */
    public function setEventsBottomBoard(\PW\BoardBundle\Document\Board $eventsBottomBoard)
    {
        $this->eventsBottomBoard = $eventsBottomBoard;
        return $this;
    }

    /**
     * Get eventsBottomBoard
     *
     * @return PW\BoardBundle\Document\Board $eventsBottomBoard
     */
    public function getEventsBottomBoard()
    {
        return $this->eventsBottomBoard;
    }

    /**
     * Set curatedTopTitle
     *
     * @param string $curatedTopTitle
     * @return Newsletter
     */
    public function setCuratedTopTitle($curatedTopTitle)
    {
        $this->curatedTopTitle = $curatedTopTitle;
        return $this;
    }

    /**
     * Get curatedTopTitle
     *
     * @return string $curatedTopTitle
     */
    public function getCuratedTopTitle()
    {
        return $this->curatedTopTitle;
    }

    /**
     * Set curatedTopImage
     *
     * @param PW\AssetBundle\Document\Asset $curatedTopImage
     * @return Newsletter
     */
    public function setCuratedTopImage($curatedTopImage)
    {
        $this->curatedTopImage = $curatedTopImage;
        return $this;
    }

    /**
     * Get curatedTopImage
     *
     * @return PW\AssetBundle\Document\Asset $curatedTopImage
     */
    public function getCuratedTopImage()
    {
        return $this->curatedTopImage;
    }

    /**
     * Set curatedTopLink
     *
     * @param string $curatedTopLink
     * @return Newsletter
     */
    public function setCuratedTopLink($curatedTopLink)
    {
        $this->curatedTopLink = $curatedTopLink;
        return $this;
    }

    /**
     * Get curatedTopLink
     *
     * @return string $curatedTopLink
     */
    public function getCuratedTopLink()
    {
        return $this->curatedTopLink;
    }

    /**
     * Set curatedTopDescription
     *
     * @param string $curatedTopDescription
     * @return Newsletter
     */
    public function setCuratedTopDescription($curatedTopDescription)
    {
        $this->curatedTopDescription = $curatedTopDescription;
        return $this;
    }

    /**
     * Get curatedTopDescription
     *
     * @return string $curatedTopDescription
     */
    public function getCuratedTopDescription()
    {
        return $this->curatedTopDescription;
    }

    /**
     * Set curatedTopContent
     *
     * @param string $curatedTopContent
     * @return Newsletter
     */
    public function setCuratedTopContent($curatedTopContent)
    {
        $this->curatedTopContent = $curatedTopContent;
        return $this;
    }

    /**
     * Get curatedTopContent
     *
     * @return string $curatedTopContent
     */
    public function getCuratedTopContent()
    {
        return $this->curatedTopContent;
    }

    /**
     * Set curatedBottomTitle
     *
     * @param string $curatedBottomTitle
     * @return Newsletter
     */
    public function setCuratedBottomTitle($curatedBottomTitle)
    {
        $this->curatedBottomTitle = $curatedBottomTitle;
        return $this;
    }

    /**
     * Get curatedBottomTitle
     *
     * @return string $curatedBottomTitle
     */
    public function getCuratedBottomTitle()
    {
        return $this->curatedBottomTitle;
    }

    /**
     * Set curatedBottomImage
     *
     * @param PW\AssetBundle\Document\Asset $curatedBottomImage
     * @return Newsletter
     */
    public function setCuratedBottomImage($curatedBottomImage)
    {
        $this->curatedBottomImage = $curatedBottomImage;
        return $this;
    }

    /**
     * Get curatedBottomImage
     *
     * @return PW\AssetBundle\Document\Asset $curatedBottomImage
     */
    public function getCuratedBottomImage()
    {
        return $this->curatedBottomImage;
    }

    /**
     * Set curatedBottomLink
     *
     * @param string $curatedBottomLink
     * @return Newsletter
     */
    public function setCuratedBottomLink($curatedBottomLink)
    {
        $this->curatedBottomLink = $curatedBottomLink;
        return $this;
    }

    /**
     * Get curatedBottomLink
     *
     * @return string $curatedBottomLink
     */
    public function getCuratedBottomLink()
    {
        return $this->curatedBottomLink;
    }

    /**
     * Set curatedBottomDescription
     *
     * @param string $curatedBottomDescription
     * @return Newsletter
     */
    public function setCuratedBottomDescription($curatedBottomDescription)
    {
        $this->curatedBottomDescription = $curatedBottomDescription;
        return $this;
    }

    /**
     * Get curatedBottomDescription
     *
     * @return string $curatedBottomDescription
     */
    public function getCuratedBottomDescription()
    {
        return $this->curatedBottomDescription;
    }

    /**
     * Set curatedBottomContent
     *
     * @param string $curatedBottomContent
     * @return Newsletter
     */
    public function setCuratedBottomContent($curatedBottomContent)
    {
        $this->curatedBottomContent = $curatedBottomContent;
        return $this;
    }

    /**
     * Get curatedBottomContent
     *
     * @return string $curatedBottomContent
     */
    public function getCuratedBottomContent()
    {
        return $this->curatedBottomContent;
    }
    
    /**
     * Set showFromYourFavoriteBrands (show fromYourFavoriteBrands section in email?)
     *
     * @param string $showFromYourFavoriteBrands
     * @return Newsletter
     */
    public function setShowFromYourFavoriteBrands($showFromYourFavoriteBrands)
    {
        $this->showFromYourFavoriteBrands = $showFromYourFavoriteBrands;
        return $this;
    }

    /**
     * Get showFromYourFavoriteBrands
     *
     * @return string $showFromYourFavoriteBrands
     */
    public function getShowFromYourFavoriteBrands()
    {
        return $this->showFromYourFavoriteBrands;
    }
    
    /**
     * Set showFromYourStream (show fromYourStream section in email?)
     *
     * @param string $showFromYourStream
     * @return Newsletter
     */
    public function setShowFromYourStream($showFromYourStream)
    {
        $this->showFromYourStream = $showFromYourStream;
        return $this;
    }
    
    /**
     * Get showFromYourStream
     *
     * @return string $showFromYourStream
     */
    public function getShowFromYourStream()
    {
        return $this->showFromYourStream;
    }    
    
    /**
     * Set showTrendingCollections (show TrendingCollections section in email?)
     *
     * @param string $showTrendingCollections
     * @return Newsletter
     */
    public function setShowTrendingCollections($showTrendingCollections)
    {
        $this->showTrendingCollections = $showTrendingCollections;
        return $this;
    }

    /**
     * Get showTrendingCollections
     *
     * @return string $showTrendingCollections
     */
    public function getShowTrendingCollections()
    {
        return $this->showTrendingCollections;
    }
    
    /**
     * Number of users to which newsletter is suppose to be sent
     *
     */
    public function getSentTo() {
        return $this->sentTo;
    }
    

    public function setSentTo($sentTo) {
        $this->sentTo = $sentTo;
    
        return $this;
    }
}
