<?php

namespace App\Console;

use DateTime;

use App\Controller;
use App\Interfaces\CommandInterface;

abstract class SustainCommand extends Controller implements CommandInterface
{
    private $runDate;

    public function isSustain()
    {
        if (!$this->runDate) {
            $this->runDate = new DateTime();
        }

        $now = new DateTime();

        $runMinute = (int) $this->runDate->format('i');
        $nowMinute = (int) $now->format('i');
        $nowSeconds = (int) $now->format('s');

        return $runMinute == $nowMinute && $nowSeconds < 58;
    }

    public function run($args)
    {
        $rounds = 0;
        $items = 0;

        while ($this->isSustain()) {

            $round = $this->round($args);

            if ($round > 0) {
                $rounds++;
                $items += $round;
            } else if ($this->isSustain()) {
                sleep(1);
            }
        }

        $this->log()->info(sprintf('COMMAND rounds=%s items=%s', $rounds, $items));
    }

    abstract public function round($context): int;
}
