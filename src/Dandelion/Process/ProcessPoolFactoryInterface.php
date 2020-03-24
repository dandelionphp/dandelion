<?php

namespace Dandelion\Process;

interface ProcessPoolFactoryInterface
{
    /**
     * @return \Dandelion\Process\ProcessPoolInterface
     */
    public function create(): ProcessPoolInterface;
}
