<?php

declare(strict_types=1);

namespace Dandelion\Operation;

use Dandelion\Configuration\ConfigurationLoaderInterface;
use Dandelion\Configuration\Repository;
use Dandelion\Console\Command\ReleaseCommand;
use Dandelion\Exception\RepositoryNotFoundException;
use Dandelion\Filesystem\FilesystemInterface;
use Dandelion\Process\ProcessFactory;
use Dandelion\Process\ProcessPoolFactoryInterface;
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
     * @var \Dandelion\Process\ProcessPoolFactoryInterface
     */
    protected $processPoolFactory;

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
     * @param \Dandelion\Process\ProcessPoolFactoryInterface $processPoolFactory
     * @param \Dandelion\VersionControl\GitInterface $git
     * @param string $binDir
     */
    public function __construct(
        ConfigurationLoaderInterface $configurationLoader,
        FilesystemInterface $filesystem,
        ProcessPoolFactoryInterface $processPoolFactory,
        GitInterface $git,
        string $binDir
    ) {
        $this->configurationLoader = $configurationLoader;
        $this->filesystem = $filesystem;
        $this->processPoolFactory = $processPoolFactory;
        $this->git = $git;
        $this->binDir = $binDir;
    }

    /**
     * @param string $repositoryName
     * @param string $branch
     *
     * @return \Dandelion\Operation\ReleaserInterface
     * @throws \Exception
     */
    public function release(
        string $repositoryName,
        string $branch
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

        $this->doRelease($branch, $repository);

        $this->filesystem->changeDirectory($currentWorkingDirectory)
            ->removeDirectory($tempDirectory);

        return $this;
    }

    /**
     * @param string $branch
     * @param \Dandelion\Configuration\Repository $repository
     *
     * @return \Dandelion\Operation\ReleaserInterface
     */
    protected function doRelease(string $branch, Repository $repository): ReleaserInterface
    {
        $version = $repository->getVersion();

        $this->git->clone($repository->getUrl(), '.')
            ->checkout($branch);

        if ($this->git->describeClosestTag($version) !== null) {
            return $this;
        }

        $this->git->tag($version)
            ->pushWithTags('origin');

        return $this;
    }

    /**
     * @param string $branch
     *
     * @return \Dandelion\Operation\ReleaserInterface
     */
    public function releaseAll(string $branch): ReleaserInterface
    {
        $configuration = $this->configurationLoader->load();
        $processPool = $this->processPoolFactory->create();

        foreach ($configuration->getRepositories() as $repositoryName => $repository) {
            $command = [
                sprintf('%sdandelion', $this->binDir),
                ReleaseCommand::NAME,
                $repositoryName,
                $branch
            ];

            $processPool->addProcessByCommand($command);
        }

        $processPool->start();

        return $this;
    }
}
