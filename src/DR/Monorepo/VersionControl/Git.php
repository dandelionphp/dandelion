<?php

namespace DR\Monorepo\VersionControl;

use DR\Monorepo\Process\ProcessFactory;
use RuntimeException;

class Git implements GitInterface
{
    /**
     * @var \DR\Monorepo\Process\ProcessFactory
     */
    protected $processFactory;

    /**
     * @param \DR\Monorepo\Process\ProcessFactory $processFactory
     */
    public function __construct(
        ProcessFactory $processFactory
    ) {
        $this->processFactory = $processFactory;
    }

    /**
     * @param string $repository
     *
     * @return \DR\Monorepo\VersionControl\GitInterface
     */
    public function clone(string $repository): GitInterface
    {
        $command = [
            'git',
            'clone',
            $repository
        ];

        $this->runCommandAsProcess($command);

        return $this;
    }

    /**
     * @param string $branch
     *
     * @return \DR\Monorepo\VersionControl\GitInterface
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
     * @return \DR\Monorepo\VersionControl\GitInterface
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
     * @return \DR\Monorepo\VersionControl\GitInterface
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
     * @return \DR\Monorepo\VersionControl\GitInterface
     */
    public function pull(string $remote, ?string $localBranch = null): GitInterface
    {
        $command = [
            'git',
            'pull',
            'remote'
        ];

        if ($localBranch !== null) {
            $command[] = $localBranch;
        }

        $this->runCommandAsProcess($command);

        return $this;
    }

    /**
     * @param string $remote
     * @param string $refSpec
     * @param bool $includeTags
     *
     * @param bool $force
     * @return \DR\Monorepo\VersionControl\GitInterface
     */
    public function push(
        string $remote,
        ?string $refSpec = null,
        bool $includeTags = false,
        bool $force = false
    ): GitInterface {
        $command = [
            'git',
            'push',
            $remote
        ];

        if ($refSpec !== null) {
            $command[] = $refSpec;
        }

        if ($includeTags) {
            $command[] = '--tags';
        }

        if ($force) {
            $command[] = '--force';
        }

        $this->runCommandAsProcess($command);

        return $this;
    }

    /**
     * @param array $command
     *
     * @return \DR\Monorepo\VersionControl\GitInterface
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