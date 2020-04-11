<?php

declare(strict_types=1);

namespace Dandelion\Process;

interface ProcessPoolFactoryInterface
{
    /**
     * @return \Dandelion\Process\ProcessPoolInterface
     */
    public function create(): ProcessPoolInterface;
}
