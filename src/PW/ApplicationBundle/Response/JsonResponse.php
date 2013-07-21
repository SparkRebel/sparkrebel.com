<?php

namespace PW\ApplicationBundle\Response;

use Symfony\Component\HttpFoundation\Response;
use Doctrine\ODM\MongoDB\Proxy\Proxy;
use PW\ApplicationBundle\Document\AbstractDocument;

/**
 * Response represents an HTTP response in JSON format.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class JsonResponse extends Response
{
    /**
     * @var array
     */
    protected $jsonified = array();
    protected $data;
    protected $callback;

    /**
     * Constructor.
     *
     * @param mixed   $data     The response data
     * @param integer $status   The response status code
     * @param array   $headers  An array of response headers
     */
    public function __construct($data = array(), $status = 200, $headers = array())
    {
        parent::__construct('', $status, $headers);

        $this->setData($data);
    }

    /**
     * {@inheritDoc}
     */
    static public function create($data = array(), $status = 200, $headers = array())
    {
        return new static($data, $status, $headers);
    }

    /**
     * Sets the JSONP callback.
     *
     * @param string $callback
     *
     * @return JsonResponse
     */
    public function setCallback($callback = null)
    {
        if ($callback) {
            // taken from http://www.geekality.net/2011/08/03/valid-javascript-identifier/
            $pattern = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u';
            if (!preg_match($pattern, $callback)) {
                throw new \InvalidArgumentException('The callback name is not valid.');
            }
        }

        $this->callback = $callback;

        return $this->update();
    }

    /**
     * Sets the data to be sent as json.
     *
     * @param mixed $data
     *
     * @return JsonResponse
     */
    public function setData($data = array())
    {
        // root should be JSON object, not array
        if (is_array($data) && 0 === count($data)) {
            $data = new \ArrayObject();
        }

        $this->data = json_encode($this->convertToArray($data));

        return $this->update();
    }

    /**
     * Updates the content and headers according to the json data and callback.
     *
     * @return JsonResponse
     */
    protected function update()
    {
        if ($this->callback) {
            // Not using application/javascript for compatibility reasons with older browsers.
            $this->headers->set('Content-Type', 'text/javascript', true);

            return $this->setContent(sprintf('%s(%s);', $this->callback, $this->data));
        }

        $this->headers->set('Content-Type', 'application/json', false);

        return $this->setContent($this->data);
    }

    /**
     * Recursively try to turn anything into an array.
     * Emphasis on *try*
     *
     * @param mixed $data
     * @param int $maxNesting Controls how deep we go ;)
     * @param int $level      Current depth
     * @return array
     */
    public function convertToArray($data, $maxNesting = 3, $level = 0)
    {
        if (is_scalar($data) || empty($data)) {
            return $data;
        } elseif (is_array($data)) {
            $maxNesting = $level == 0 ? $maxNesting++ : $maxNesting;
        } elseif (is_object($data)) {
            // Limit what we actually try to return as an array
            if ($data instanceOf \Traversable) {
                $data = iterator_to_array($data);
            } elseif ($data instanceOf \ArrayAccess) {
                // Don't keep converting the same object
                $hash = spl_object_hash($data);
                if (isset($this->jsonified[$hash])) {
                    return $data;
                } else {
                    $this->jsonified[$hash] = true;
                }
                // Make sure data is loaded if we have a Proxy
                if ($data instanceOf Proxy && !$data->__isInitialized__) {
                    $data->__load();
                }
                if ($data instanceOf AbstractDocument) {
                    $data = $data->toArray();
                } else {
                    $data = (array) $data;
                }
            } else {
                return $data;
            }
        }

        foreach ($data as $key => $value) {
            $level++;
            if ($level <= $maxNesting) {
                $data[$key] = $this->convertToArray($value, $maxNesting, $level);
            }
            $level--;
            if (empty($data[$key])) {
                unset($data[$key]);
            }
        }

        return $data;
    }
}