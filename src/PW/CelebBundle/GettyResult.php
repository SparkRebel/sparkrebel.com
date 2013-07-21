<?php

namespace PW\CelebBundle;

class GettyResult
{
    protected $struct;
    protected $class;

    public function parseDate($field)
    {
        // incoming format: /Date(1294868991374-0800)/
        $time  = substr($field, 6, -2);
        $parts = explode("-", $time);
        $miliseconds = substr($time, 0, 13);
        $timestamp = intval($miliseconds/1000);
        $timezone_hours = substr($time, 14, 2);
        $timestamp -= $timezone_hours*3600;
        $date  = new \DateTime;
        $date->setTimestamp($timestamp);
        return $date;
    }

    public function __construct(\stdClass $rawOutput, GettyImage $class)
    {
        $this->class  = $class;
        $this->struct = $rawOutput;
    }

    function getRank()
    {
        if (!empty($this->struct->CollectionName)) {
            $ourPartnerNames = array('AFP');
            if (in_array($this->struct->CollectionName,$ourPartnerNames)) {
                return 1; // this is image from our partner -> we return highest rank, so image is valid for sure
            }
        }
        // return QualityRank of image
        return empty($this->struct->QualityRank) ? 0 : $this->struct->QualityRank;
    }

    public function getArtist()
    {
        return $this->struct->Artist;
    }

    public function getCopyright()
    {
        return $this->getArtist() . '/' . $this->struct->CollectionName;
    }

    public function getMeta()
    {
        $meta = $this->struct;
        foreach (array('Keywords', 'SizesDownloadableImages', 'UrlAttachment') as $del) {
            unset($meta->$del);
        }
        $meta->copyright = $this->getCopyright();
        return $meta;
    }

    public function getCreated()
    {
        return $this->parseDate($this->struct->DateCreated);
    }

    public function merge(\stdClass $obj)
    {
        foreach ($obj as $key => $value) {
            $this->struct->$key = $value;
        }
    }

    public function getId()
    {
        return 'getty:' . $this->struct->ImageId;
    }

    public function getDownloadUrl()
    {
        if (empty($this->struct->UrlAttachment)) {
            $details = $this->class->getDownloadDetails($this->struct->ImageId);
            if (empty($details) || empty($details->Images)) {
                throw new \RuntimeException("Cannot read image for {$this->struct->ImageId}");
            }
            $this->merge(current($details->Images));
        }
        return $this->struct->UrlAttachment;
    }

    public function getTitle()
    {
        return $this->struct->Title;
    }

    public function getDescription()
    {
        return $this->struct->Caption;
    }

    public function getTags()
    {
        $keywords = array();
        if (isset($this->struct->Keywords)) {
            foreach ((array)$this->struct->Keywords as $keyword) {
                $keywords[] = $keyword->Text;
            }
        }
        return $keywords;
    }
}
