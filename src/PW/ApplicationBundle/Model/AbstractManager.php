<?php

namespace PW\ApplicationBundle\Model;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\SoftDelete\SoftDeleteManager;
use Doctrine\ODM\MongoDB\SoftDelete\SoftDeleteable;
use PW\UserBundle\Document\User;
use PW\UserBundle\Model\UserManager;
use PW\ApplicationBundle\Document\Exception\ConstraintViolationException;

abstract class AbstractManager implements ManagerInterface, ContainerAwareInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $dm;

    /**
     * @var \Doctrine\ODM\MongoDB\SoftDelete\SoftDeleteManager
     */
    protected $sdm;

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var \PW\UserBundle\Model\UserManager
     */
    protected $userManager;

    /**
     * @var \Symfony\Component\Validator\ValidatorInterface
     */
    protected $validator;

    /**
     * @var array
     */
    protected $flushOptions = array(
        'safe'  => true,
        'fsync' => false,
    );

    /**
     * @param DocumentManager $dm
     * @param string $class
     */
    public function __construct(DocumentManager $dm, $class = null)
    {
        $this->dm = $dm;
        if ($class !== null) {
            $this->setClass($class);
        }
    }

    /**
     * @param array $data
     */
    public function create(array $data = array())
    {
        $class    = $this->getClass();
        $document = new $class();
        if (!empty($data) && method_exists($document, 'fromArray')) {
            $document->fromArray($data);
        }
        return $document;
    }

    /**
     * @param mixed $document
     * @param mixed $groups
     * @return \Symfony\Component\Validator\ConstraintViolationList
     */
    public function validate($document, $groups = null)
    {
        if ($groups === true) {
            $groups = null;
        }

        $errors = $this->getValidator()->validate($document, $groups);

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    /**
     * Update the passed document
     *
     * @param mixed $document any object
     * @param mixed $andFlush flush the document manager when done?
     * @param mixed $validate auto-validate?
     * @return \Symfony\Component\Validator\ConstraintViolationList
     * @deprecated
     */
    public function update($document, $andFlush = true, $validate = false)
    {
        return $this->save($document, array(
            'flush'    => $andFlush,
            'validate' => $validate,
        ));
    }

    /**
     * Save the passed in document
     *
     * @param mixed $document
     * @param array $options
     * @throws \PW\ApplicationBundle\Document\Exception\ConstraintViolationException
     * @return \Symfony\Component\Validator\ConstraintViolationList
     */
    public function save($document, array $options = array())
    {
        $options += array(
            'exceptions' => true,
            'validate'   => true,
            'flush'      => true,
        );

        if ($options['validate']) {
            if (($violations = $this->validate($document, $options['validate'])) !== true) {
                if ($options['exceptions']) {
                    throw new ConstraintViolationException($violations);
                } else {
                    return $violations;
                }
            }
        }

        $this->dm->persist($document);

        if ($options['flush']) {
            $this->dm->flush($document, $this->flushOptions);
        }

        return true;
    }

    /**
     * @return void
     */
    public function flush()
    {
        $this->dm->flush();
        if ($this->getSoftDeleteManager()) {
            $this->getSoftDeleteManager()->flush();
        }
    }

    /**
     * @param mixed $document
     * @param User $deletedBy
     * @param bool $safe
     * @param bool $andFlush
     * @throws Exception
     */
    public function delete($document, User $deletedBy = null, $safe = true, $andFlush = true)
    {
        if ($safe) {
            if ($document instanceOf SoftDeleteable) {
                if ($deletedBy) {
                    $document->setIsActive(false);
                    $document->setDeletedBy($deletedBy);
                    $this->dm->persist($document);
                } else {
                    $document->setIsActive(false);
                    $this->dm->persist($document);
                }
                $this->getSoftDeleteManager()->delete($document);
                if ($andFlush) {
                    $this->dm->flush();
                    $this->getSoftDeleteManager()->flush();
                }
            } else {
                $document->setDeleted(new \DateTime());
                $document->setIsActive(false);
                if ($deletedBy) {
                    $document->setDeletedBy($deletedBy);
                }
                $this->dm->persist($document);
                if ($andFlush) {
                    $this->dm->flush();
                }
            }
        } else {
            $this->dm->remove($document);
            if ($andFlush) {
                $this->dm->flush();
            }
        }
    }

    public function unDelete($document, $andFlush = true)
    {
        $document->setIsActive(true);
        $document->setDeleted(null);
        if(method_exists($document, 'setDeletedBy')) {
            $document->setDeletedBy(null);
        }
        $this->dm->persist($document);
        if ($andFlush) {
            $this->dm->flush();
        }
    }

    /**
     * @param mixed $document
     * @param User $deletedBy
     * @param bool $safe
     * @param bool $andFlush
     * @throws Exception
     */
    public function deleteAll($documents, User $deletedBy = null, $safe = true, $andFlush = true)
    {
        if ($documents instanceOf \Doctrine\ODM\MongoDB\Query\Builder) {
            if ($safe) {
                $documents->field('deleted')->set(new \DateTime());
                $documents->field('isActive')->set(false);
                if (!empty($deletedBy)) {
                    $documents->field('deletedBy')->set($deletedBy);
                }
                $documents->getQuery()->execute();
            } else {
                throw new \Exception('Unsafe deletion for QueryBuilder is not implemented (yet).');
            }
        } else {
            foreach ($documents as $document) {
                $this->delete($document, $deletedBy, $safe, false);
            }
            if ($andFlush) {
                $this->dm->flush();
            }
        }
    }

    /**
     * @param string $id
     * @return mixed
     * @deprecated
     */
    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @param array $ids
     * @return mixed
     * @deprecated
     */
    public function findByIds(array $ids)
    {
        return $this->getRepository()
            ->findByIds($ids)
            ->getQuery()
            ->execute();
    }

    /**
     * @return \Doctrine\ODM\MongoDB\DocumentRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->repository = $this->dm->getRepository($class);
        $this->class      = $this->dm->getClassMetadata($class)->name;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param \PW\UserBundle\Model\UserManager $userManager
     */
    public function setUserManager(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @return \PW\UserBundle\Model\UserManager $userManager
     */
    public function getUserManager($userManager)
    {
        if (!($this->userManager instanceOf UserManager)) {
            $this->userManager = $this->container->get('pw_user.user_manager');
        }

        $this->userManager = $userManager;
    }

    /**
     * @param \Symfony\Component\Validator\ValidatorInterface $validator
     */
    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @return \Symfony\Component\Validator\Validator
     */
    public function getValidator()
    {
        if (!($this->validator instanceOf ValidatorInterface)) {
            $this->validator = $this->container->get('validator');
        }

        return $this->validator;
    }

    /**
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    public function getDocumentManager()
    {
        if (!($this->dm instanceOf DocumentManager)) {
            $this->dm = $this->container->get('doctrine_mongodb.odm.document_manager');
        }

        return $this->dm;
    }

    /**
     * @param \Doctrine\ODM\MongoDB\SoftDelete\SoftDeleteManager $sdm
     */
    public function setSoftDeleteManager(SoftDeleteManager $sdm)
    {
        $this->sdm = $sdm;
    }

    /**
     * @return \Doctrine\ODM\MongoDB\SoftDelete\SoftDeleteManager
     */
    public function getSoftDeleteManager()
    {
        if (!($this->sdm instanceOf SoftDeleteManager)) {
            $this->sdm = $this->container->get('doctrine.odm.mongodb.soft_delete.manager');
        }

        return $this->sdm;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
