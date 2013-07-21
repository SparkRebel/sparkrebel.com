<?php

namespace PW\ApplicationBundle\Provider;

class StreamProvider
{
    /**
     * @var type
     */
    protected $session;

    /**
     * @param type $session
     */
    public function __construct($session = null)
    {
        $this->session = $session;
    }

    /**
     * @param type $stream
     */
    public function resetStream($stream)
    {
        $this->session->remove($this->getStreamSessionAttr($stream));
        $this->session->save();
    }

    /**
     * @param type $stream
     * @param type $timestamp
     */
    public function setNextTimeCriteria($stream, $timestamp)
    {
        $this->session->set($this->getStreamSessionAttr($stream), $timestamp);
        $this->session->save();
    }

    /**
     * @param type $stream
     * @return type
     */
    public function getNextTimeCriteria($stream)
    {
        return $this->session->get($this->getStreamSessionAttr($stream));
    }

    /**
     * @param type $stream
     * @return string
     */
    protected function getStreamSessionAttr($stream)
    {
        return 'stream-' . $stream;
    }
}
