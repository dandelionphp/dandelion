<?php

namespace Dandelion\Process;

interface ProcessPoolInterface
{
    /**
     * @param string[] $command
     *
     * @return \Dandelion\Process\ProcessPoolInterface
     */
    public function addProcessByCommand(array $command): ProcessPoolInterface;

    /**
     * @return \Dandelion\Process\ProcessPoolInterface
     */
    public function start(): ProcessPoolInterface;
}
