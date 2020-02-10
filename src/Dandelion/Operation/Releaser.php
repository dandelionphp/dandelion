<?php

declare(strict_types=1);

namespace Dandelion\Operation;

use Dandelion\Configuration\ConfigurationLoaderInterface;
use Dandelion\Exception\RepositoryNotFoundException;
use Dandelion\Filesystem\FilesystemInterface;
use Dandelion\Process\ProcessFactory;
use Dandelion\VersionControl\GitInterface;

use function sprintf;

class Releaser implements ReleaserInterface
{
    /**
     * @var \Dandelion\Configuration\ConfigurationLoaderInterface
     */
    protected $configurationLoader;

    /**
     * @var \Dandelion\Filesystem\FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var \Dandelion\Process\ProcessFactory
     */
    protected $processFactory;

    /**
     * @var \Dandelion\VersionControl\GitInterface
     */
    protected $git;

    /**
     * @var string
     */
    protected $binDir;

    /**
     * @param \Dandelion\Configuration\ConfigurationLoaderInterface $configurationLoader
     * @param \Dandelion\Filesystem\FilesystemInterface $filesystem
     * @param \Dandelion\Process\ProcessFactory $processFactory
     * @param \Dandelion\VersionControl\GitInterface $git
     * @param string $binDir
     */
    public function __construct(
        ConfigurationLoaderInterface $configurationLoader,
        FilesystemInterface $filesystem,
        ProcessFactory $processFactory,
        GitInterface $git,
        string $binDir
    ) {
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
     * @return \Dandelion\Operation\ReleaserInterface
     * @throws \Exception
     */
    public function release(
        string $repositoryName,
        string $branch,
        string $version
    ): ReleaserInterface {
        $configuration = $this->configurationLoader->load();
        $repositories = $configuration->getRepositories();

        if (!$repositories->offsetExists($repositoryName)) {
            throw new RepositoryNotFoundException(sprintf('Could not find repository "%s".', $repositoryName));
        }

        $repository = $repositories->offsetGet($repositoryName);
        $tempDirectory = rtrim($configuration->getPathToTempDirectory(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . $repositoryName . DIRECTORY_SEPARATOR;

        $currentWorkingDirectory = $this->filesystem->getCurrentWorkingDirectory();
        $this->filesystem->changeDirectory($tempDirectory);

        $this->git->clone($repository->getUrl(), '.')
            ->checkout($branch)
            ->tag($version)
            ->pushWithTags('origin');

        $this->filesystem->changeDirectory($currentWorkingDirectory)
            ->removeDirectory($tempDirectory);

        return $this;
    }

    /**
     * @param string $branch
     * @param string $version
     *
     * @return \Dandelion\Operation\ReleaserInterface
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
     * @return \Dandelion\Operation\ReleaserInterface
     */
    protected function releaseAsProcess(
        string $repositoryName,
        string $branch,
        string $version
    ): ReleaserInterface {
        $command = $command = [
            sprintf('%sdandelion', $this->binDir),
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
