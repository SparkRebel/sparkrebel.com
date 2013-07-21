<?php

namespace PW\ApplicationBundle\PHPUnit;

class HtmlResultPrinter extends \PHPUnit_TextUI_ResultPrinter
{
    public function __construct($out = null, $verbose = false, $colors = false, $debug = false)
    {
        // Start output buffering, so we can send the output to the browser in chunks
        ob_start();
        $this->autoFlush = true;
        parent::__construct($out, $verbose, false, $debug);
    }

    public function write($buffer)
    {
        $buffer = nl2br($buffer);

        // Pad the string, otherwise the browser will do nothing with the flushed output
        $buffer = str_pad($buffer, 1024) . "\n";

        if ($this->out) {
            fwrite($this->out, $buffer);
            if ($this->autoFlush) {
                $this->incrementalFlush();
            }
        } else {
            print $buffer;
            if ($this->autoFlush) {
                $this->incrementalFlush();
            }
        }
    }

    public function incrementalFlush()
    {
        if ($this->out) {
            fflush($this->out);
        } else {
            // Flush the buffered output
            ob_flush();
            flush();
        }
    }
}
