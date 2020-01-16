<?php

namespace DR\Monorepo\Operation;

use DR\Monorepo\Configuration\ConfigurationLoaderInterface;
use DR\Monorepo\Exception\RepositoryNotFoundException;
use DR\Monorepo\Filesystem\FilesystemInterface;
use DR\Monorepo\Process\ProcessFactory;
use DR\Monorepo\VersionControl\GitInterface;
use function sprintf;

class Releaser implements ReleaserInterface
{
    /**
     * @var \DR\Monorepo\Configuration\ConfigurationLoaderInterface
     */
    protected $configurationLoader;

    /**
     * @var \DR\Monorepo\Filesystem\FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var \DR\Monorepo\Process\ProcessFactory
     */
    protected $processFactory;

    /**
     * @var \DR\Monorepo\VersionControl\GitInterface
     */
    protected $git;

    /**
     * @var string
     */
    protected $binDir;

    /**
     * @param \DR\Monorepo\Configuration\ConfigurationLoaderInterface $configurationLoader
     * @param \DR\Monorepo\Filesystem\FilesystemInterface $filesystem
     * @param \DR\Monorepo\Process\ProcessFactory $processFactory
     * @param \DR\Monorepo\VersionControl\GitInterface $git
     * @param string $binDir
     */
    public function __construct(
        ConfigurationLoaderInterface $configurationLoader,
        FilesystemInterface $filesystem,
        ProcessFactory $processFactory,
        GitInterface $git,
        string $binDir
    )
    {
        $this->configurationLoader = $configurationLoader;
        $this->filesystem = $filesystem;
        $this->processFactory = $processFactory;
        $this->git = $git;
        $this->binDir = $binDir;
    }

    /**
     * @param string $repositoryName
     * @param string $branch
     * @param string $version
     *
     * @return \DR\Monorepo\Operation\ReleaserInterface
     * @throws \Exception
     */
    public function release(
        string $repositoryName,
        string $branch,
        string $version
    ): ReleaserInterface
    {
        $configuration = $this->configurationLoader->load();
        $repositories = $configuration->getRepositories();

        if (!$repositories->offsetExists($repositoryName)) {
            throw new RepositoryNotFoundException(sprintf('Could not find repository "%s".', $repositoryName));
        }

        $repository = $repositories->offsetGet($repositoryName);
        $tempDirectory = sprintf('%s/%s/', rtrim($configuration->getPathToTempDirectory(), '/'), $repositoryName);

        $currentWorkingDirectory = $this->filesystem->getCurrentWorkingDirectory();
        $this->filesystem->changeDirectory($tempDirectory);

        $this->git->clone($repository->getUrl(), '.')
            ->checkout($branch)
            ->tag($version)
            ->push('origin', null, true);

        $this->filesystem->changeDirectory($currentWorkingDirectory)
            ->removeDirectory($tempDirectory);

        return $this;
    }

    /**
     * @param string $branch
     * @param string $version
     *
     * @return \DR\Monorepo\Operation\ReleaserInterface
     */
    public function releaseAll(string $branch, string $version): ReleaserInterface
    {
        $configuration = $this->configurationLoader->load();

        foreach ($configuration->getRepositories() as $repositoryName => $repository) {
            $this->releaseAsProcess($repositoryName, $branch, $version);
        }

        return $this;
    }

    /**
     * @param string $repositoryName
     * @param string $branch
     * @param string $version
     *
     * @return \DR\Monorepo\Operation\ReleaserInterface
     */
    protected function releaseAsProcess(
        string $repositoryName,
        string $branch,
        string $version
    ): ReleaserInterface
    {
        $command = $command = [
            sprintf('%smonorepo', $this->binDir),
            'release',
            $repositoryName,
            $branch,
            $version
        ];

        $process = $this->processFactory->create($command);

        $process->start();

        return $this;
    }
}