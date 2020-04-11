<?php

declare(strict_types=1);

namespace Dandelion\Process;

use Closure;

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
     * @var \Closure[]
     */
    protected $callbacks;

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
        $this->processes = [];
        $this->callbacks = [];
        $this->runningProcesses = [];
        $this->processFactory = $processFactory;
    }

    /**
     * @param string[] $command
     * @param \Closure|null $callback
     *
     * @return \Dandelion\Process\ProcessPoolInterface
     */
    public function addProcess(array $command, ?Closure $callback = null): ProcessPoolInterface
    {
        $this->processes[] = $this->processFactory->create($command);
        $this->callbacks[] = $callback;

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

        foreach ($this->processes as $process) {
            $this->runningProcesses[] = $process;
            $process->start();
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

                if ($this->callbacks[$index] !== null) {
                    $this->callbacks[$index]($runningProcess);
                }

                unset($this->runningProcesses[$index]);
            }

            sleep(1);
        }

        return $this;
    }
}
