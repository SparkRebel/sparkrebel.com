<?php

namespace PW\FlagBundle\Extension;

use PW\FlagBundle\Document\FlagSummary;

class FlagRatio extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'flag_by_ratio' => new \Twig_Filter_Method($this, 'flagByRatio'),
            'flag_against_ratio' => new \Twig_Filter_Method($this, 'flagAgainstRatio'),
        );
    }

    /**
     * @param FlagSummary $flagSummary
     * @return string
     */
    public function flagByRatio(FlagSummary $flagSummary = null)
    {
        $kills  = (int) $flagSummary->getTotalByApproved();
        $deaths = (int) $flagSummary->getTotalByRejected();

        if ($kills > $deaths && $kills > 2) {
            $html = '<span class="helpful">%s</span>:<span>%s</span>';
        } elseif ($kills < $deaths && $deaths > 2) {
            $html = '<span>%s</span>:<span class="unhelpful">%s</span>';
        } else {
            $html = '<span>%s</span>:<span>%s</span>';
        }

        return sprintf("<small class=\"kd\">({$html})</small>", $kills, $deaths);
    }

    /**
     * @param FlagSummary $flagSummary
     * @return string
     */
    public function flagAgainstRatio(FlagSummary $flagSummary = null)
    {
        $kills  = (int) $flagSummary->getTotalAgainstApproved();
        $deaths = (int) $flagSummary->getTotalAgainstRejected();

        if ($kills > $deaths && $kills > 2) {
            $html = '<span class="unhelpful">%s</span>:<span>%s</span>';
        } elseif ($kills < $deaths && $deaths > 2) {
            $html = '<span>%s</span>:<span class="helpful">%s</span>';
        } else {
            $html = '<span>%s</span>:<span>%s</span>';
        }

        return sprintf("<small class=\"kd\">({$html})</small>", $kills, $deaths);
    }

    public function getName()
    {
        return 'flag_ratio';
    }
}