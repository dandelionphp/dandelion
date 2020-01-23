<?php

declare(strict_types=1);

namespace Dandelion\VersionControl;

use Dandelion\Exception\RuntimeException;
use Dandelion\Process\ProcessFactory;

class Git implements GitInterface
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
     * @param string $repository
     * @param string|null $directory
     *
     * @return \Dandelion\VersionControl\GitInterface
     */
    public function clone(string $repository, string $directory = null): GitInterface
    {
        $command = [
            'git',
            'clone',
            $repository
        ];

        if ($directory !== null) {
            $command[] = $directory;
        }

        return $this->runCommandAsProcess($command);
    }

    /**
     * @param string $branch
     *
     * @return \Dandelion\VersionControl\GitInterface
     */
    public function checkout(string $branch): GitInterface
    {
        $command = [
            'git',
            'checkout',
            $branch
        ];

        return $this->runCommandAsProcess($command);
    }

    /**
     * @param string $tagName
     *
     * @return \Dandelion\VersionControl\GitInterface
     */
    public function tag(string $tagName): GitInterface
    {
        $command = [
            'git',
            'tag',
            $tagName
        ];

        return $this->runCommandAsProcess($command);
    }

    /**
     * @param string $name
     * @param string $url
     *
     * @return \Dandelion\VersionControl\GitInterface
     */
    public function addRemote(string $name, string $url): GitInterface
    {
        $command = [
            'git',
            'remote',
            'add',
            $name,
            $url
        ];

        return $this->runCommandAsProcess($command);
    }

    /**
     * @param string $remote
     * @param string|null $localBranch
     *
     * @return \Dandelion\VersionControl\GitInterface
     */
    public function pull(string $remote, ?string $localBranch = null): GitInterface
    {
        $command = [
            'git',
            'pull',
            $remote
        ];

        if ($localBranch !== null) {
            $command[] = $localBranch;
        }

        return $this->runCommandAsProcess($command);
    }

    /**
     * @param string $remote
     * @param string|null $refSpec
     *
     * @return \Dandelion\VersionControl\GitInterface
     */
    public function push(
        string $remote,
        ?string $refSpec = null
    ): GitInterface {
        $command = $this->createPushCommand($remote, $refSpec);

        return $this->runCommandAsProcess($command);
    }

    /**
     * @param string $remote
     * @param string|null $refSpec
     *
     * @return \Dandelion\VersionControl\GitInterface
     */
    public function pushForcefully(string $remote, ?string $refSpec = null): GitInterface
    {
        $command = $this->createPushCommand($remote, $refSpec);

        $command[] = '--force';

        return $this->runCommandAsProcess($command);
    }

    /**
     * @param string $remote
     *
     * @return \Dandelion\VersionControl\GitInterface
     */
    public function pushWithTags(string $remote): GitInterface
    {
        $command = $this->createPushCommand($remote);

        $command[] = '--tags';

        return $this->runCommandAsProcess($command);
    }

    /**
     * @param string $remote
     * @param string|null $refSpec
     *
     * @return string[]
     */
    protected function createPushCommand(string $remote, ?string $refSpec = null): array
    {
        $command = [
            'git',
            'push',
            $remote
        ];

        if ($refSpec !== null) {
            $command[] = $refSpec;
        }

        return $command;
    }

    /**
     * @param string[] $command
     *
     * @return \Dandelion\VersionControl\GitInterface
     */
    protected function runCommandAsProcess(array $command): GitInterface
    {
        $process = $this->processFactory->create($command);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getExitCodeText(), $process->getExitCode());
        }

        return $this;
    }
}
