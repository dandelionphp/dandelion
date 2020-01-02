<?php

namespace DR\Monorepo\Process;

use Symfony\Component\Process\Process;

class ProcessFactory
{
    /**
     * @param array $command
     * @return \Symfony\Component\Process\Process
     */
    public function create(array $command): Process
    {
        return new Process($command);
    }
}
