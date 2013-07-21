<?php
namespace PW\CelebBundle;

use Iterator;

class GettyCursor implements Iterator
{
    protected $current = null;
    protected $buffer = array();
    protected $perPage = 50;
    protected $index   = 0;
    protected $offset  = 0;
    protected $limit   = 1000;
    protected $people  = false;
    protected $getty = null;

    /**
     * Reset this class data to default values
     * @return $this
     */
    public function setDefaultData() {
        $this->current = null;
        $this->buffer = array();
        $this->perPage = 50;
        $this->index   = 0;
        $this->offset  = 0;
        $this->limit   = 1000;
        $this->people  = false;
        $this->getty = null;
        return $this;
    }
    
    protected function doQuery()
    {
        if (empty($this->getty)) {
            $this->getty = new GettyImage;
        }
        $skip = $this->index + $this->offset + 1;
        $this->buffer = array_merge(
            $this->buffer,
            $this->getty->Search($this->search, $this->perPage, $skip, false, $this->people)
        );
        return $this;
    }

    public function limit($limit)
    {
        if (!is_numeric($limit) || $limit < 1) {
            throw new \InvalidArgumentException('$limit must be a positive integer');
        }
        $this->limit = (int) $limit;
        return $this;
    }

    public function peopleSearch($people = true)
    {
        $this->people = $people;
        return $this;
    }

    public function search($search)
    {
        $this->search = $search;
        return $this;
    }

    public function skip($int)
    {
        $this->offset = (int)($int > 0 ? $int : 0);
        return $this;
    }

    public function current()
    {
        if (empty($this->current)) {
            $this->current = $this->buffer[$this->index];
        }
        return $this->current;
    }

    public function key()
    {
        return $this->current->getId();
    }

    public function valid()
    {
        if ($this->limit && $this->index > $this->limit) {
            return false;
        }
        $valid = false;
        for ($i=0; $i < 5; $i++) {
            for ($e=&$this->index; $e < count($this->buffer); $e++) {
                if ($this->buffer[$e]->getRank() <= 2) {
                    $valid = true;
                    break 2;
                }
            }
            // Read more pages if no result has an aceptable rank
            $this->doQuery();
        }
        return $valid;
    }

    public function next()
    {
        $this->index++;
        $this->current = null;
    }

    public function rewind()
    {
        $this->index = 0;
    }
}
