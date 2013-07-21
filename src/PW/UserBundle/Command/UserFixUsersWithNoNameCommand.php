<?php

namespace PW\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

class UserFixUsersWithNoNameCommand extends ContainerAwareCommand
{
           
    protected $userManager;
        

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('user:without-name:refresh-all')
            ->setDescription('Select all users without names and assign Anonymous-N to them')
            ->setDefinition(array());
    }

    /**
     * Replaces all user names with Anonymous-N so the links wont be broken
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        /* @var $userManager \PW\UserBundle\Model\UserManager */
        $this->userManager = $this->getContainer()->get('pw_user.user_manager');       
            
        $users = $this->userManager->getRepository()
            ->createQueryBuilder()
            ->field('isActive')->equals(true)
            ->field('name')->equals(null)
            ->getQuery()->execute();
        
        $pattern = new \MongoRegex("/^Anonymous(-\d+)?$/");
        
        $anon_counter = 1;
        $latest_anon = $this->userManager->getRepository()
                   ->createQueryBuilder()
                   ->field('isActive')->equals(true)
                   ->field('name')->equals($pattern)
                   ->sort('name', 'desc')
                   ->getQuery()->getSingleResult();
        
        if($latest_anon) {
            preg_match($pattern->__toString(), $latest_anon->getName(), $matches);
            $anon_counter = (int)abs($matches[1]) + 1;
        }
        
        if(count($users) === 0) {
            $output->writeln("<info>No users with null name</info>");
            return;
        }
        
        foreach ($users as $user) {
            $anon_string = "Anonymous-".$anon_counter;
            $output->writeln("");
            $user->setName($anon_string);
            $this->userManager->update($user);
            $anon_counter++;
            $output->writeln("<info>Updated User with id {$user->getId()} name string to {$anon_string}</info>");            
        }
               
    }



}
