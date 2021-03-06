<?php

declare(strict_types=1);

namespace Sukhoykin\App\Util;

use Psr\Log\LoggerInterface;

class Stdout implements LoggerInterface
{
    public function emergency($message, array $context = array())
    {
        $this->log('EMERGENCY', $message, $context);
    }

    public function alert($message, array $context = array())
    {
        $this->log('ALERT', $message, $context);
    }

    public function critical($message, array $context = array())
    {
        $this->log('CRITICAL', $message, $context);
    }

    public function error($message, array $context = array())
    {
        $this->log('ERROR', $message, $context);
    }

    public function warning($message, array $context = array())
    {
        $this->log('WARNING', $message, $context);
    }

    public function notice($message, array $context = array())
    {
        $this->log('NOTICE', $message, $context);
    }

    public function info($message, array $context = array())
    {
        $this->log('INFO', $message, $context);
    }

    public function debug($message, array $context = array())
    {
        $this->log('DEBUG', $message, $context);
    }

    public function log($level, $message, array $context = array())
    {
        echo $level, ' ', $message, "\n";
    }
}
