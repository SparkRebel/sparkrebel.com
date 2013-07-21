<?php

namespace PW\InviteBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\ArrayInput,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * list invite requests that weren't redeemed
 */
class ListUnusedInvitesCommand extends ContainerAwareCommand
{

    protected $dm;

    protected $requestRepo;

    protected $inviteRepo;

    protected $userRepo;

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('invite:list:unused')
            ->setDescription('list invite requests that weren\'t redeemed')
            ->setDefinition(array());
    }

    /**
     * execute
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->dm = $this->getContainer()
            ->get('doctrine_mongodb.odm.document_manager');
        $this->userRepo = $this->dm->getRepository('PWUserBundle:User');
        $this->requestRepo = $this->dm->getRepository('PWInviteBundle:Request');

        $unused = array();
        $nocode = array();
        $someoneElse = array();

        $requests = $this->requestRepo->findBy(array());

        $usedCount = $usedWithoutCodeCount = 0;

        foreach ($requests as $request) {
            $email = $request->getEmail();
            $code = $request->getCode();
            if (empty($code)) {
                $nocode []= $email;
                continue;
            }

            $user = $this->userRepo->findOneBy(array('email' => $email));
            if (!$user) {
                $unused []= $email;

                if ($code->getUsedCount() > 0) {
                    $used = $code->getUsedBy();
                    foreach ($used as $use) {
                        $someoneElse[$email . ' ' . $use->getId()] = $use->getEmail();
                    }

                }
            }
        }

        $output->write("codes which were used by other people:\n");
        $output->write(json_encode($someoneElse) . "\n");

        $output->write("users that received a code but didn't join:\n");
        $output->write(json_encode($unused) . "\n");

    }

}
