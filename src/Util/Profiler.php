<?php

declare(strict_types=1);

namespace App\Util;

class Profiler
{
    private $benches = [];
    private $starts = [];
    private $totals = [];
    private $completes = [];

    public function start($name)
    {
        $this->benches[$name] = microtime(true);
    }

    public function took($name)
    {
        return microtime(true) - $this->benches[$name];
    }

    public function startProgress($name, $total)
    {
        $this->starts[$name] = time();
        $this->totals[$name] = $total;
        $this->completes[$name] = 0;
    }

    public function updateProgress($name, $complete = 1)
    {
        $this->completes[$name] += $complete;
    }

    public function getProgress($name)
    {
        $workTime = time() - $this->starts[$name];
        $itemsPerSecond = $this->completes[$name] / $workTime;
        $etaSeconds = (int) (($this->totals[$name] - $this->completes[$name]) / $itemsPerSecond);

        return [$this->completes[$name], $this->totals[$name], $itemsPerSecond, $etaSeconds];
    }
}
