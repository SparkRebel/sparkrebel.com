<?php

namespace PW\ApplicationBundle\EventListener;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs,
    Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs,
    Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Automatically sets createdBy and modifiedBy fields based upon who is
 * logged in when an action occurs
 */
class ItemCreateListener extends ContainerAware
{
    /**
     * Before creating a new row in the db - set createdBy
     * if not already set and the logged in user can be identified
     *
     * @param LifecycleEventArgs $args instance
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $user = $this->getLoggedInUser();
        if (!$user) {
            return;
        }

        $document = $args->getDocument();
        if (method_exists($document, 'setCreatedBy')) {
            $document->setCreatedBy($user);
        }
    }

    /**
     * Before updating a row in the db - always set modifiedBy
     * if the logged in user can be identified
     *
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $user = $this->getLoggedInUser();
        if (!$user) {
            return;
        }

        $document = $args->getDocument();
        if (method_exists($document, 'setModifiedBy')) {
            $document->setModifiedBy($user);

            // We are doing a update, so we must force Doctrine to update the
            // changeset in case we changed something above
            $dm   = $args->getDocumentManager();
            $uow  = $dm->getUnitOfWork();
            $meta = $dm->getClassMetadata(get_class($document));
            $uow->recomputeSingleDocumentChangeSet($meta, $document);
        }
    }

    /**
     * Reads from the session, via the security.context provider to know who is logged in
     *
     * @return user instance or false if there is no logged in user
     */
    protected function getLoggedInUser()
    {
        if (PHP_SAPI === 'cli') {
            return false;
        }

        $securityToken = $this->container->get('security.context')->getToken();
        if (!$securityToken) {
            return false;
        }

        $user = $securityToken->getUser();
        if (!is_object($user)) {
            return false;
        }

        return $user;
    }
}
