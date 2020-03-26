<?php

namespace Dandelion\Process;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

class ProcessPool implements ProcessPoolInterface
{
    /**
     * @var \Symfony\Component\Process\Process[]
     */
    protected $runningProcesses;

    /**
     * @var \Symfony\Component\Process\Process[]
     */
    protected $processes;

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
        $this->processes = [];
        $this->runningProcesses = [];
        $this->processFactory = $processFactory;
        $this->logger = $logger;
    }


    /**
     * @param string[] $command
     *
     * @return \Dandelion\Process\ProcessPoolInterface
     */
    public function addProcessByCommand(array $command): ProcessPoolInterface
    {
        $this->processes[] = $this->processFactory->create($command);

        return $this;
    }

    /**
     * @return \Dandelion\Process\ProcessPoolInterface
     */
    public function start(): ProcessPoolInterface
    {
        if (count($this->processes) === 0) {
            return $this;
        }

        $logger = $this->logger;

        foreach ($this->processes as $process) {
            $this->runningProcesses[] = $process->start(static function (string $type, string $data) use ($logger) {
                // @codeCoverageIgnoreStart
                $logLevel = Logger::NOTICE;

                if ($type === Process::ERR) {
                    $logLevel = Logger::ERROR;
                }

                $logger->log($logLevel, $data);
                // @codeCoverageIgnoreEnd
            });
        }

        return $this->wait();
    }

    /**
     * @return \Dandelion\Process\ProcessPoolInterface
     */
    protected function wait(): ProcessPoolInterface
    {
        while (!empty($this->runningProcesses)) {
            foreach ($this->runningProcesses as $index => $runningProcess) {
                if ($runningProcess->isRunning()) {
                    continue;
                }

                unset($this->runningProcesses[$index]);
            }

            sleep(1);
        }

        return $this;
    }
}
