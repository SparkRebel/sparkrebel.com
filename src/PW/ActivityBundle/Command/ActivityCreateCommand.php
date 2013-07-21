<?php

namespace PW\ActivityBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    PW\ActivityBundle\Document\Activity;

class ActivityCreateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('activity:create')
            ->setDescription('Add activity for one user')
            ->setDefinition(array(
                new InputArgument('event', InputArgument::REQUIRED, 'The event name'),
                new InputArgument('id', InputArgument::REQUIRED, 'The main id for whatever the activity relates to'),
                new InputArgument('userId', InputArgument::OPTIONAL, 'The user id to create the activity for. Only used where the id is ambiguous (comment tagging)')
            ));
    }

    /**
     * Call process with the passed args
     *
     * Inherited by all the commands in this bundle
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');

        $event = $input->getArgument('event');
        $id = $input->getArgument('id');
        $userId = $input->getArgument('userId');

        $this->process($event, $id, $userId);
        $this->dm->flush(null, array('safe' => false, 'fsync' => false));
    }

    /**
     * Create Activity for friends
     *
     * @param string $event  event name
     * @param string $id     id for the main entity in the activity
     * @param string $userId the user id to process, optional and only used for comment tagging
     */
    protected function process($event, $id, $userId = null)
    {
        $method = str_replace('.', '', $event);

        $data = $this->$method($id, $userId);
        if (!$data) {
            $this->output->writeln("<comment>Activity *not* created for {$event} ({$id})</comment>");
            return;
        }

        if (!$data['target'])  {
            $this->output->writeln("<comment>Activity *not* created for {$event} ({$id}). Missing target</comment>");
            return;
        }

        $data['type'] = $event;
        if (empty($data['created'])) {
            $data['created'] = $data['target']->getCreated();
        }
        if (empty($data['createdBy'])) {
            $data['createdBy'] = $data['target']->getCreatedBy();
        }
        if (empty($data['user'])) {
            $data['user'] = $data['createdBy'];
        }
        
        if (!empty($data['data'])) {
            foreach ($data['data'] as &$value) {
                if (is_callable(array($value, 'getId'))) {
                    $value = $value->getId();
                }
            }
        }
        
        if (!$data['user'] || !$data['user']->getIsActive()) {
            $this->output->writeln("<comment>Notification *not* created for {$event} ({$id}) - user not active or doesnt exists</comment>");
            return;
        }

        if ($data['user']->hasDisabledNotifications()) {
            $this->output->writeln("<comment>Notification *not* created for {$event} ({$id}) - user has disabled notifications</comment>");
            return;
        }

        $activity = new Activity($data);
        $html = $this->render($event, $activity);
        $activity->setHtml($html);

        $this->dm->persist($activity);
        $this->dm->flush(null, array('safe' => false, 'fsync' => false));

        if (!$userId) {
            $userId = $data['user']->getId();
        }
        $this->output->writeln("<info>Activity created for {$event} (id: {$id} - userId: {$userId})</info>");

        // TODO should be a property of user
        $friends = $this->dm->getRepository('PWUserBundle:Follow')->createQueryBuilder()
            ->count()
            ->field('follower')->references($data['user'])
            ->field('isFriend')->equals(true)
            ->field('target.$ref')->equals('users')
            ->getQuery()->execute();

        if ($friends) {
            $this->output->writeln("<info>... and notifying {$friends} friends</info>");
            return $this->getContainer()->get('pw.event')->publish(
                'notify.friends',
                array('activityId' => $activity->getId())
            );
        }
    }

    /**
     * Add activity unless the user is also following the user. If they are following the user
     * aswell, that means this event was triggered as part of a cascade as the user is automatically
     * following all of the boards the user as created, or that the target user has just created.
     * In either case the follower has not explicitly followed any board.
     *
     * @param string $id target id
     *
     * @return array
     */
    protected function boardFollow($id)
    {
        $followRepo = $this->dm->getRepository('PWUserBundle:Follow');
        $return['target'] = $followRepo->find($id);
        if (!$return['target']) {
            return false;
        }
        $return['user'] = $return['target']->getFollower();

        $alsoFollowingUser = $followRepo->createQueryBuilder()
            ->count()
            ->field('follower')->references($return['user'])
            ->field('isActive')->equals(true)
            ->field('target')->references($return['target']->getUser())
            ->getQuery()->execute();

        if ($alsoFollowingUser) {
            return false;
        }
        return $return;
    }

    /**
     * commentCreate
     *
     * If the comment has no post it's a reply.
     *
     * @param string $id target id
     *
     * @return array
     */
    protected function commentCreate($id)
    {
        $return['target'] = $this->dm->getRepository('PWPostBundle:PostComment')->find($id);

        if (!$return['target']->getPost()) {
            return false;
        }

        return $return;
    }

    /**
     * commentReply
     *
     * TODO all that "what am I replying to" logic should be unnecessary. we have the wrong schema.
     *
     * @param string $id target id
     *
     * @return array
     */
    protected function commentReply($id)
    {
        $commentRepo = $this->dm->getRepository('PWPostBundle:PostComment');
        $return['target'] = $commentRepo->find($id);
        $return['user'] = $return['target']->getCreatedBy();

        $return['replyTo'] = $commentRepo->createQueryBuilder()
            ->field('subactivity')->references($return['target'])
            ->getQuery()->execute()->getSingleResult();
        if (!$return['replyTo']) {
            return false;
        }

        $return['post'] = $return['replyTo']->getPost();

        $image = $return['post']->getImage();
        if ($image) {
            $image = $image->getUrl();
        } else {
            $image = null;
        }

        $return['data'] = array(
            'post' => array(
                'id' => $return['post']->getId(),
                'description' => $return['post']->getDescription(),
                'image' => $image
            )
        );

        return $return;
    }

    /**
     * commentTag
     *
     * @param string $id     target id
     * @param string $userId The id of the user that has been tagged
     *
     * @return array
     */
    protected function commentTag($id, $userId)
    {
        $return['target'] = $this->dm->getRepository('PWPostBundle:PostComment')->find($id);
        $return['user'] = $this->dm->getRepository('PWUserBundle:User')->find($userId);
        return $return;
    }

    /**
     * postCreate
     *
     * @param string $id target id
     *
     * @return array
     */
    protected function postCreate($id)
    {
        $return['target'] = $this->dm->getRepository('PWPostBundle:Post')->find($id);
        return $return;
    }

    /**
     * userFollow
     *
     * @param string $id target id
     *
     * @return array
     */
    protected function userFollow($id)
    {
        $return['target'] = $this->dm->getRepository('PWUserBundle:Follow')->find($id);
        if(!$return['target']) {
            return $return;
        }
        $return['user'] = $return['target']->getFollower();
        return $return;
    }

    /**
     * render
     *
     * @param string $template the event name, most likely
     * @param object $activity passed to the template to render
     * @param string $format   html or (in the future, for email alerts) text
     *
     * @return string
     */
    protected function render($template, $activity, $format = 'html')
    {
        $view = "PWActivityBundle:Activity:partials/$template.$format.twig";
        $parameters = compact('activity');
        return $this->getContainer()->get('templating')->render($view, $parameters);
    }
}
