<?php

namespace Dandelion\Process;

use Psr\Log\LoggerInterface;

class ProcessPoolFactory implements ProcessPoolFactoryInterface
{
    /**
     * @var \Dandelion\Process\ProcessFactory
     */
    protected $processFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Dandelion\Process\ProcessFactory $processFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        ProcessFactory $processFactory,
        LoggerInterface $logger
    ) {
        $this->processFactory = $processFactory;
        $this->logger = $logger;
    }

    /**
     * @return \Dandelion\Process\ProcessPoolInterface
     */
    public function create(): ProcessPoolInterface
    {
        return new ProcessPool(
            $this->processFactory,
            $this->logger
        );
    }
}
