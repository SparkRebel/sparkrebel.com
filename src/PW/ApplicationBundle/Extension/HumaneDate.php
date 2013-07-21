<?php

namespace PW\ApplicationBundle\Extension;

class HumaneDate extends \Twig_Extension
{
    const MINUTE = 60;
    const HOUR   = 3600;
    const DAY    = 86400;
    const WEEK   = 604800;
    const MONTH  = 2629743;
    const YEAR   = 31556926;

    /**
     * @var array
     */
    private $formats;

    public function __construct()
    {
        // if diff < [0] then if count = 2 use [1] else diff/[2] + [1]
        $this->formats = array(
            array(0, 'just now'),
            array(2, '1 second'),
            array(59, 'seconds', 1),
            array(self::MINUTE * 1.5, '1 minute'),
            array(3600, 'minutes', 60),
            array(self::HOUR * 1.5, '1 hour'),
            array(86400, 'hours', 3600),
            array(self::DAY * 1.5, '1 day'),
            array(604800, 'days', 86400),
            array(self::WEEK * 1.5, '1 week'),
            array(2628000, 'weeks', 907200),
            array(self::MONTH * 1.5, '1 month'),
            array(31536000, 'months', 2628000),
            array(self::YEAR * 1.5, '1 year'),
            array(3153600000, 'years', 31536000),
        );
    }

    public function getFilters()
    {
        return array(
            'humane_date' => new \Twig_Filter_Method($this, 'humaneDate', array('is_safe' => array('html'))),
        );
    }

    /**
     * Return a usable date
     *
     * @param string $date
     * @param array $config
     * @return string
     */
    public function humaneDate($date = null, $config = array())
    {
        $config = $this->parseConfig($date, $config);

        if ($config['max'] && $config['diff'] >= $config['max']) {
            $config['display'] = date($config['max_format'], $config['from']);
        } elseif ($config['min'] && $config['diff'] < $config['min']) {
            $config['display'] = $config['min_display'];
        } else {
            foreach ($this->formats as $format) {
                if ($format[0] >= $config['min'] && $config['diff'] < $format[0]) {
                    if (count($format) === 2) {
                        $config['display'] = $format[1];
                        break;
                    } else {
                        $config['display'] = ceil($config['diff'] / $format[2]) . ' ' . $format[1];
                        break;
                    }
                }
            }

            if (preg_match('/[\d]/', $config['display'])) {
                if (!empty($config['prefix'])) {
                    $config['display'] = "{$config['prefix']} {$config['display']}";
                }
                if (!empty($config['suffix'])) {
                    $config['display'] .= " {$config['suffix']}";
                }
            }
        }

        // Special cases
        if ($config['display'] == '1 day ago') {
            $config['display'] = 'yesterday';
        }

        return sprintf('<abbr title="%s"%s>%s</abbr>',
            date('c', $config['from']),
            !empty($config['class']) ? ' class="' . $config['class'] . '"' : '',
            $config['display']
        );
    }

    /**
     * @param array $config
     * @return array
     */
    private function parseConfig($date = null, $config = array())
    {
        $defaults = array(
            'from'        => $date,
            'to'          => time(),
            'min'         => false,
            'min_display' => 'just now',
            'max'         => false,
            'max_format'  => 'F d Y',
            'class'       => 'timeago',
            'display'     => null,
            'prefix'      => 'about',
            'suffix'      => 'ago',
            'relative'    => true,
        );

        $config = array_merge($defaults, (array) $config);

        if (!empty($config['from'])) {
            $config['from'] = $this->convertToTimestamp($config['from']);
        }

        if (!empty($config['to'])) {
            $config['to'] = $this->convertToTimestamp($config['to']);
        }

        if (!empty($config['max'])) {
            $config['max'] = $this->convertNameToSeconds($config['max']);
        }

        if (!empty($config['min'])) {
            $config['min'] = $this->convertNameToSeconds($config['min']);
        }

        $config['diff'] = $config['to'] - $config['from'];

        if ($config['diff'] > self::HOUR) {
            $config['class'] = trim(str_replace('timeago', '', $config['class']));
        }

        return $config;
    }

    /**
     * @param string $name
     * @return int
     */
    private function convertNameToSeconds($name)
    {
        switch (strtolower($name)) {
            case 'year':
                return self::YEAR;
                break;
            case 'month':
                return self::MONTH;
                break;
            case 'week':
                return self::WEEK;
                break;
            case 'day':
                return self::DAY;
                break;
            case 'minute':
                return self::MINUTE;
                break;
            case 'second':
            default:
                return 1;
                break;
        }
    }

    /**
     * @param mixed $date
     * @return int
     */
    private function convertToTimestamp($date)
    {
        if (is_int($date)) {
            return $date;
        } elseif ($date instanceOf \DateTime) {
            return $date->getTimestamp();
        } else {
            return strtotime($date);
        }
    }

    public function getName()
    {
        return 'pw_humane_date';
    }
}
