<?php

namespace PW\ActivityBundle\Extension;

use PW\ApplicationBundle\Document\Email;

class EmailSubject extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'get_subject_from_notifications' => new \Twig_Function_Method($this, 'getSubject'),
        );
    }

    /**
     * @param ArrayCollection $notifications
     * @param \PW\ApplicationBundle\Document\Email $email
     */
    public function getSubject($notifications = array(), Email $email = null)
    {
        if ($email) {
            $data = $email->getData();
        } else {
            $data = $this->getCounts($notifications);
        }

        $userCount = count($data['users']);
        if ($userCount === 1) {
            //
            // One user...
            $subject = $user = current($data['users']);
            if (count($data['types']) === 1) {
                //
                // ... did one thing
                foreach ($data['types'] as $typeData) {
                    $typeCount = $typeData['count'];
                    switch ($typeData['name']) {
                        case 'user.follow':
                            $subject .= ' is now following you';
                            break 2;
                        case 'board.follow':
                            $subject .= ' is now following your collection';
                            if ($typeCount > 1) {
                                $subject .= 's';
                            }
                            break 2;
                        case 'post.repost':
                            $subject .= ' resparked your spark';
                            if ($typeCount > 1) {
                                $subject .= 's';
                            }
                            break 2;
                        case 'comment.reply':
                            $subject .= ' replied to your comment';
                            if ($typeCount > 1) {
                                $subject .= 's';
                            }
                            break 2;
                        case 'comment.create':
                            $subject .= ' commented on your spark';
                            if ($typeCount > 1) {
                                $subject .= 's';
                            }
                            break 2;
                    }
                }
            } else {
                //
                // ... did many things
                $subject .= ' ' . $this->handleMultipleTypes($data);
            }
        } else {
            //
            // Multiple users...
            if (count($data['types']) === 1) {
                //
                // ... did one thing
                foreach ($data['types'] as $typeData) {
                    $typeCount = $typeData['count'];
                    switch ($typeData['name']) {
                        case 'user.follow':
                            $subject = "You have {$typeCount} new followers";
                            break 2;
                        case 'board.follow':
                            $subject = "You have {$typeCount} new collection followers";
                            break 2;
                        case 'post.repost':
                            $subject = "{$userCount} members have resparked your sparks";
                            break 2;
                        case 'comment.reply':
                            $subject = "{$userCount} members have replied to your comments";
                            break 2;
                        case 'comment.create':
                            $subject = "{$userCount} members have commented on your sparks";
                            break 2;
                    }
                }
            } else {
                //
                // ... did many things
                $subject = 'Members have ' . $this->handleMultipleTypes($data);
            }
        }

        return $subject . ' on SparkRebel';
    }

    /**
     * Fallback in case getData() is empty
     *
     * @param array $notifications
     * @return array
     */
    protected function getCounts($notifications = array())
    {
        $data = array(
            'users' => array(),
            'types' => array(),
        );

        foreach ($notifications as $notification /* @var $notification \PW\ActivityBundle\Document\Notification */) {
            // Dots in field names are not allowed
            $type  = $notification->getType();
            $found = false;
            foreach ($data['types'] as $i => $typeData) {
                if ($typeData['name'] == $type) {
                    $data['types'][$i]['count'] += 1; $found = true;
                    break;
                }
            }

            // Not found, add it...
            if (!$found) {
                $data['types'][] = array(
                    'name'  => $type,
                    'count' => 1,
                );
            }

            if ($type == 'user.follow' || $type == 'board.follow') {
                if ($name = $notification->getTarget()->getFollower()->getName()) {
                    if (!in_array($name, $data['users'])) {
                        $data['users'][] = $name;
                    }
                }
            } else {
                if ($name = $notification->getCreatedBy()->getName()) {
                    if (!in_array($name, $data['users'])) {
                        $data['users'][] = $name;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param array $data
     * @return string
     */
    protected function handleMultipleTypes(array $data = array())
    {
        $types = array();
        foreach ($data['types'] as $typeData) {
            $types[$typeData['name']] = $typeData['count'];
        }

        // ... did many things
        $subject = array();

        // Following
        if (isset($types['user.follow'])) {
            $part = 'followed you';
            if (isset($types['board.follow'])) {
                $part .= ' and your collection';
                if ($types['board.follow'] > 1) {
                    $part .= 's';
                }
            }
            $subject[] = $part;
        } else {
            if (isset($types['board.follow'])) {
                $part = 'followed your collection';
                if ($types['board.follow'] > 1) {
                    $part .= 's';
                }
                $subject[] = $part;
            }
        }

        // Sparks
        if (isset($types['post.repost'])) {
            $part = 'resparked';
            if (isset($types['comment.create'])) {
                $subject[] = $part;
                $part = 'commented on your spark';
                if ($types['post.repost'] > 1 || $types['comment.create'] > 1) {
                    $part .= 's';
                }
            } else {
                $part .= ' your spark';
                if ($types['post.repost'] > 1) {
                    $part .= 's';
                }
            }
            $subject[] = $part;
        } elseif (isset($types['comment.create'])) {
            $part .= 'commented on your spark';
            if ($types['comment.create'] > 1) {
                $part .= 's';
            }
            $subject[] = $part;
        }

        // Comments
        if (isset($types['comment.reply'])) {
            $part = 'replied to your comment';
            if ($types['comment.reply'] > 1) {
                $part .= 's';
            }
            $subject[] = $part;
        }

        $parts = count($subject);
        if (!empty($subject)) {
            if ($parts > 1) {
                $subject[$parts - 1] = 'and ' . $subject[$parts - 1];
            }
        } else {
            // Fallback?
            $subject[] = 'interacted with you';
        }
        
        return implode(($parts > 2 ? ', ' : ' '), $subject);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'emailsubject';
    }
}