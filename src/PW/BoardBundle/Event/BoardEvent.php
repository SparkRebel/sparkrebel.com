<?php

namespace PW\BoardBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use PW\BoardBundle\Document\Board;

class BoardEvent extends Event
{
    /**
     * @var \PW\BoardBundle\Document\Board
     */
    protected $board;

    /**
     * @param \PW\BoardBundle\Document\Board $board
     */
    public function __construct(Board $board)
    {
        $this->board = $board;
    }

    /**
     * @return \PW\BoardBundle\Document\Board
     */
    public function getBoard()
    {
        return $this->board;
    }
}