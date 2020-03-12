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

        $this->runCommandAsProcess($command);

        return $this;
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

        $this->runCommandAsProcess($command);

        return $this;
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

        $this->runCommandAsProcess($command);

        return $this;
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

        $this->runCommandAsProcess($command);

        return $this;
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

        $this->runCommandAsProcess($command);

        return $this;
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

        $this->runCommandAsProcess($command);

        return $this;
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

        $this->runCommandAsProcess($command);

        return $this;
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

        $this->runCommandAsProcess($command);

        return $this;
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
     * @return string
     */
    protected function runCommandAsProcess(array $command): string
    {
        $process = $this->processFactory->create($command);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getExitCodeText(), $process->getExitCode());
        }

        return $process->getOutput();
    }

    /**
     * @param string|null $match
     *
     * @return string|null
     */
    public function describeClosestTag(?string $match = null): ?string
    {
        $command = [
            'git',
            'describe',
            '--tags',
            '--abbrev=0'
        ];

        if ($match !== null) {
            $command[] = '--match';
            $command[] = \sprintf('\'%s\'', $match);
        }

        try {
            return $this->runCommandAsProcess($command);
        } catch (RuntimeException $e) {
            return null;
        }
    }

    /**
     * @param string $remote
     *
     * @return bool
     */
    public function existsRemote(string $remote): bool
    {
        $command = [
            'git',
            'remote',
            'show',
            $remote
        ];

        try {
            $this->runCommandAsProcess($command);
        } catch (RuntimeException $e) {
            return false;
        }

        return true;
    }
}
