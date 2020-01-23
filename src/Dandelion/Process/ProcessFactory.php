<?php

declare(strict_types=1);

namespace Dandelion\Process;

use Symfony\Component\Process\Process;

class ProcessFactory
{
    /**
     * @param string[] $command
     *
     * @return \Symfony\Component\Process\Process<string, string>
     */
    public function create(array $command): Process
    {
        return new Process($command);
    }
}
