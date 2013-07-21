<?php

namespace PW\ApplicationBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\SerializerBundle\Annotation as API;

/**
 * @MongoDB\MappedSuperclass
 */
abstract class AbstractDocument implements \ArrayAccess
{
    /**
     * @var bool
     * @MongoDB\Boolean
     * @API\Exclude
     */
    protected $isActive;

    /**
     * @var \DateTime
     * @MongoDB\Date
     * @API\Exclude
     */
    protected $created;

    /**
     * @var \DateTime
     * @MongoDB\Date
     * @API\Exclude
     */
    protected $modified;

    /**
     * @var \DateTime
     * @MongoDB\Date
     * @API\Exclude
     */
    protected $deleted;

    /**
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->init();

        if ($this->isActive == null) {
            if ($this->deleted) {
                $this->isActive = false;
            } else {
                $this->isActive = true;
            }
        }

        $this->fromArray($data);
    }

    /**
     * Initialization ran before construction
     */
    public function init()
    {
    }

    /**
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->getDeleted();
    }

    /**
     * @param mixed $object
     * @return boolean
     */
    public function equals($object)
    {
        if (!is_a($object, get_class($this))) {
            return false;
        }

        if (method_exists($object, 'getId') && method_exists($this, 'getId')) {
            if ($this->getId() !== $object->getId()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function fromArray(array $data = array())
    {
        return self::staticFromArray($this, $data);
    }

    /**
     * @param mixed $object
     * @param array $data
     * @return mixed
     */
    public static function staticFromArray($object, array $data = array())
    {
        foreach ($data as $key => $value) {
            if ($value !== null) {
                $method = 'set' . ucfirst($key);
                if (method_exists($object, $method)) {
                    $object->{$method}($value);
                }
            }
        }
        return $object;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = array();
        if (method_exists($this, 'getId')) {
            $data['id'] = $this->getId();
        }
        foreach ($this as $key => $value) {
            $method = 'get' . ucfirst($key);
            if (method_exists($this, $method)) {
                $data[$key] = $this->{$method}();
            } else {
                $data[$key] = $value;
            }
        }
        return $data;
    }

    /**
     * @param mixed $object
     * @return array
     */
    public static function staticToArray($object)
    {
        $data = array('id' => $object->getId());
        foreach (get_class_methods($object) as $method) {
            $key = lcfirst(str_replace('get', '', $method));
            if (substr($method, 0, 3) == 'get' && method_exists($object, "set{$key}")) {
                $data[$key] = $object->{$method}();
            }
        }
        return $data;
    }

    /**
     * @return array
     */
    public function getAdminData()
    {
        $data = $this->toArray();

        $data['stats'] = 'no stats';
        if (!empty($this->stats)) {
            $stats = array();
            foreach ($this->stats as $id => $stat) {
                $id = explode(":", $stat->getId());
                if (strlen($stat->getDate()) != 7) continue;
                $stats[ $id[1] . ' : ' . $id [0] ] = $stat->getTotal();
            }
            $data['stats'] = $stats;
        } 

        return self::_handleAdminData($data);
    }

    /**
     * @return string
     */
    public function getAdminValue()
    {
        return $this->getId();
    }

    /**
     * @param mixed $object
     * @return array
     */
    public static function staticGetAdminData($object)
    {
        $data = self::staticToArray($object);
        return self::_handleAdminData($data);
    }

    /**
     * @param array $data
     * @return array
     */
    protected static function _handleAdminData(array $data = array())
    {
        $data['id'] = $data['display'] = $data['id'];
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                if (method_exists($value, 'getAdminValue')) {
                     $data[$key] = $value->getAdminValue();
                }
            }
        }
        return $data;
    }

    /**
     * setIsActive
     *
     * @param bool $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
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
     * @return \DateTime
     */
    public function getLastModified()
    {
        if ($modified = $this->getModified()) {
            return $modified;
        }

        return $this->getCreated();
    }

    /**
     * Set deleted
     *
     * @param \DateTime $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted  = $deleted;
        $this->isActive = false;
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
     * Required by ArrayAccess implementation
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetExists($offset)
    {
        $offset = ucfirst($offset);
        $value  = $this->{"get{$offset}"}();
        return $value !== null;
    }

    /**
     * Required by ArrayAccess implementation
     *
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $offset = ucfirst($offset);
        $this->{"set{$offset}"}($value);
    }

    /**
     * Required by ArrayAccess implementation
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $offset = ucfirst($offset);
        return $this->{"get{$offset}"}();
    }

    /**
     * Required by ArrayAccess implementation
     *
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        $offset = ucfirst($offset);
        $this->{"set{$offset}"}(null);
    }
}
