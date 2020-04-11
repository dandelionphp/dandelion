<?php

declare(strict_types=1);

namespace Dandelion\Process;

class ProcessPoolFactory implements ProcessPoolFactoryInterface
{
    /**
     * @var \Dandelion\Process\ProcessFactory
     */
    protected $processFactory;

    /**
     * @param \Dandelion\Process\ProcessFactory $processFactory
     */
    public function __construct(
        ProcessFactory $processFactory
    ) {
        $this->processFactory = $processFactory;
    }

    /**
     * @return \Dandelion\Process\ProcessPoolInterface
     */
    public function create(): ProcessPoolInterface
    {
        return new ProcessPool(
            $this->processFactory
        );
    }
}
