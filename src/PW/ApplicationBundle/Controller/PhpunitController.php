<?php

namespace PW\ApplicationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PhpunitController extends Controller
{
    public function runTestsAction($filterClass = null)
    {
        // Make sure PHPUnit is autoloaded
        require_once('PHPUnit/Autoload.php');

        set_time_limit(0);
        $version = \PHPUnit_Runner_Version::id();

        $kernel_dir = $this->container->getParameter('kernel.root_dir');
        chdir($kernel_dir);

        // This will force the printer class to be autoloaded by Symfony, before PHPUnit tries to (and does not) find it
        $printerClass = 'PW\ApplicationBundle\PHPUnit\HtmlResultPrinter';
        if (!class_exists($printerClass)) {
            $printerClass = false;
        }

        $argv = array('phpunit');
        if ($filterClass) {
            $argv[] = '--filter';
            $argv[] = $filterClass;
        }

        if (version_compare($version, "3.6.0") >= 0) {

            if ($printerClass) {
                $argv[] = '--printer';
                $argv[] = $printerClass;
            }

            $_SERVER['argv'] = $argv;
            \PHPUnit_TextUI_Command::main(true);

        } else {

            ob_end_clean();
            echo '<pre>';

            $_SERVER['argv'] = $argv;
            \PHPUnit_TextUI_Command::main(false);

            echo '</pre>';
            exit;

        }
    }
}
