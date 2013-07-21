<?php

namespace PW\BoardBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert,
    PW\BoardBundle\Document\Board;

class CelebBoard
{
    /**
     * @Assert\Type(type="PW\BoardBundle\Document\Board")
     * @Assert\Valid
     */
    protected $board;

    
    /**
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    protected $newIcon;
    
    /**
     * @param Board $board
     */
    public function __construct(Board $board = null)
    {
        $this->board = $board;
    }

    /**
     * @param Board $board
     */
    public function setBoard(Board $board)
    {
        $this->board = $board;
        return $this;
    }

    /**
     * @return \PW\BoardBundle\Document\Board
     */
    public function getBoard()
    {
        return $this->board;
    }

    /**
     */
    public function setNewIcon($newIcon)
    {
        $this->newIcon = $newIcon;
    }

    /**
     */
    public function getNewIcon()
    {
        return $this->newIcon;
    }
}
