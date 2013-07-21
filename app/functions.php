<?php

/**
 * Dump
 *
 * @param mixed $thing to be dumped
 * @param int   $i     the index in the stack trace to report as here-ish
 */
function d($thing, $i = 0)
{
    // Show line number
    $trace = debug_backtrace();
    $lines = array();
    for ($j = $i; $j <= ($i + 1); $j++) {
        if (isset($trace[$j]['line'])) {
            $lines[$trace[$j]['line']] = $trace[$j]['file'];
        }
    }

    if (PHP_SAPI == 'cli') {
        echo "==============================\n";
    } else {
        echo '</script></style><div style="border: 2px solid red; background-color: white; padding: 5px">';
    }

    $count = 1;
    foreach ($lines as $number => $file) {
        $prefix = null;
        if ($count > 1) {
            $prefix = ' ' . str_repeat('-', $count - 1) . ' ';
        }
        if (PHP_SAPI == 'cli') {
            echo "{$prefix}In {$file} around line {$number}\n";
        } else {
            if (defined('XDEBUG_FILE_LINK_FORMAT') && $link = XDEBUG_FILE_LINK_FORMAT) { //ini_get('xdebug.file_link_format')) {
                $link = str_replace('%f', $file, $link);
                $link = str_replace('%l', $number, $link);
                echo "<pre>{$prefix}In <b><a href='{$link}'>{$file}</a></b> around line <b>{$number}</b></pre>";
            } else {
                echo "<pre>{$prefix}In <b>{$file}</b> around line <b>{$number}</b></pre>";
            }
        }
        $count++;
    }

    if (PHP_SAPI == 'cli') {
        echo "==============================\n";
    }

    // Dump
    if (function_exists('ladybug_dump')) {
        ladybug_set('general.expanded', true);
        ladybug_set('object.max_nesting_level', 4);
        ladybug_dump($thing);
    } elseif (extension_loaded('xdebug')) {
        var_dump($thing);
    } else {
        echo '<pre>'; var_dump($thing); echo '</pre>';
    }

    if (PHP_SAPI == 'cli') {
        echo "\n\n";
    } else {
        echo '</div><br>';
    }
}

/**
 * Dump and die.
 *
 * @param mixed $thing to be dumped
 */
function dd($thing)
{
    d($thing, 1);
    exit;
}

/**
 * remoteIp
 *
 * loop on the various permutations of remote ip address that we can read - retrieve them
 * and return the first valid ip address. takes care of proxies, see reference for more info
 *
 * @link http://www.kavoir.com/2010/03/php-how-to-detect-get-the-real-client-ip-address-of-website-visitors.html
 * @access public
 * @return string - a valid ip address, or false
 */
function remoteIp($reset = false) {
    if (empty($_server)) {
        return false;
    }

    static $return;

    if ($return !== null && !$reset) {
        return $return;
    }

    $keys = array(
        'http_client_ip',
        'http_x_forwarded_for',
        'http_x_forwarded',
        'http_x_cluster_client_ip',
        'http_forwarded_for',
        'http_forwarded',
        'remote_addr'
    );

    foreach ($keys as $key) {
        if (array_key_exists($key, $_server) === true) {
            foreach (explode(',', $_server[$key]) as $ip) {
                if (filter_var($ip, filter_validate_ip) !== false) {
                    $return = $ip;
                    return $ip;
                }
            }
        }
    }

    $return = false;
    return false;
}
