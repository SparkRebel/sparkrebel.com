<?php

namespace PW\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\SecurityExtraBundle\Annotation\Secure;
use PW\ApplicationBundle\Response\JsonResponse;

/**
 * Encapsulates all our admin functionality
 *
 * @Route("/admin")
 */
class DefaultController extends Controller
{
    protected $classMap = array(
        'Area'         => 'PWCategoryBundle:Area',
        'Brand'        => 'PWUserBundle:Brand',
        'Event'        => 'PWApplicationBundle:Event',
        'FeedItem'     => 'PWItemBundle:FeedItem',
        'Merchant'     => 'PWUserBundle:Merchant',
        'Notification' => 'PWActivityBundle:Notification',
        'Partner'      => 'PWUserBundle:Partner',
        'PostComment'  => 'PWPostBundle:PostComment',
        'Follow'       => 'PWUserBundle:Follow',
        'Source'       => 'PWAssetBundle:Source'
    );

    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Method("GET")
     * @Route("/setField/{class}/{id}/{field}/{value}")
     */
    public function setFieldAction($class, $id, $field, $value)
    {
        $result    = array('status' => 'ko');
        $fullClass = $this->_getClass($class);
        if (!$fullClass) {
            return new JsonResponse($result);
        }

        $dm     = $this->container->get('doctrine_mongodb.odm.document_manager');
        $object = $dm->getRepository($fullClass)->find($id);
        $setter = 'set' . ucfirst($field);
        if (method_exists($object, $setter)) {
            $object->$setter($value);
            $dm->persist($object);
            $dm->flush($object);
            $result = array('status' => 'ok');
        }

        return new JsonResponse($result);
    }

    public function loadStats($object)
    {
        $id = $object->getid();
        if (strlen($id) == 24 && preg_match("/^[0-9a-f]+$/i", $id)) {
            $id = new \MongoID($id);
        }
        $dm   = $this->container->get('doctrine_mongodb.odm.document_manager');
        $stats = $dm->createQueryBuilder('PWStatsBundle:Summary')
            ->field('reference.$id')->equals($id)
            ->sort('$natural', 'desc')
            ->getQuery()->execute();
        $object->stats = iterator_to_array($stats);
    }

    /**
     * Return a json object of the data associated with the requested document
     *
     * @Secure(roles="ROLE_ADMIN")
     * @Method({"GET", "POST"})
     * @Route("/data/{class}/{id}")
     */
    public function dataAction($class, $id)
    {
        $result    = array();
        $fullClass = $this->_getClass($class);
        if (!$fullClass) {
            return new JsonResponse($result);
        }

        $dm = $this->container->get('doctrine_mongodb.odm.document_manager');
        if (strpos($id, ',') || $id === '*') {
            $qb = $dm->createQueryBuilder($fullClass);
            if (strpos($id, ',')) {
                $ids = explode(',', $id);
                $qb->field('id')->in($ids);
            }
            $objects = $qb->getQuery()->execute();
            foreach ($objects as $object) {
                $this->loadStats($object);
                $result[$object->getId()] = $this->_formatRow($object, $fullClass);
            }
        } else {
            $object = $dm->getRepository($fullClass)->find($id);
            $this->loadStats($object);
            $result = $this->_formatRow($object, $fullClass, $id);
        }

        return new JsonResponse($result);
    }

    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Method({"GET", "POST"})
     * @Route("/data/{class}")
     */
    public function bulkDataAction($class)
    {
        $result    = array();
        $fullClass = $this->_getClass($class);
        if (!$fullClass) {
            return new JsonResponse($result);
        }

        $dm   = $this->container->get('doctrine_mongodb.odm.document_manager');
        $data = json_decode(file_get_contents('php://input'), true);

        if ($data) {
            $qb = $dm->createQueryBuilder($fullClass);
            $qb->field('_id')->in($data);
            $objects = $qb->getQuery()->execute();
            foreach ($objects as $object) {
                $this->loadStats($object);
                $result[$object->getId()] = $this->_formatRow($object, $fullClass);
            }
        }

        return new JsonResponse($result);
    }

    /**
     * @param string $class a one word class name
     * @return string the full class name
     */
    protected function _getClass($class)
    {
        if ($class === 'undefined') {
            return false;
        }

        $class = ucfirst($class);
        if (!empty($this->classMap[$class])) {
            return $this->classMap[$class];
        }

        return "PW{$class}Bundle:{$class}";
    }

    /**
     * @param object $row   any document instance
     * @param string $class the requested class
     * @param string $id    the requested id
     * @return array
     */
    protected function _formatRow($row, $class, $id = null)
    {
        if ($row) {
            if (is_callable(array($row, 'getAdminData'))) {
                $result = $row->getAdminData();
                $result = array_filter($result);
                foreach ($result as $key => $row) {
                    if (is_object($row)) {
                        if ($row instanceOf \DateTime) {
                            $result[$key] = $row->format('Y-m-d H:i:s');
                        }
                    }
                }
                ksort($result);
            } else {
                $result = array('error' => "{$class} does not implement getAdminData");
            }
        } else {
            $result = array('error' => "{$class}: {$id} does not exist");
        }

        return $result;
    }
}
