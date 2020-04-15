<?php

declare(strict_types=1);

namespace Dandelion\Process;

use Closure;

interface ProcessPoolInterface
{
    /**
     * @param string[] $command
     *
     * @param \Closure|null $callback
     * @return \Dandelion\Process\ProcessPoolInterface
     */
    public function addProcess(array $command, ?Closure $callback = null): ProcessPoolInterface;

    /**
     * @return \Dandelion\Process\ProcessPoolInterface
     */
    public function start(): ProcessPoolInterface;
}
