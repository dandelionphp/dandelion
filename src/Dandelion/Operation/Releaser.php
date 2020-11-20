<?php

declare(strict_types=1);

namespace Dandelion\Operation;

use Dandelion\Configuration\ConfigurationLoaderInterface;
use Dandelion\Configuration\Repository;
use Dandelion\Console\Command\ReleaseCommand;
use Dandelion\Exception\RepositoryNotFoundException;
use Dandelion\Filesystem\FilesystemInterface;
use Dandelion\Operation\Result\MessageFactoryInterface;
use Dandelion\Process\ProcessPoolFactoryInterface;
use Dandelion\VersionControl\GitInterface;

use function getenv;
use function sprintf;

class Releaser extends AbstractOperation
{
    /**
     * @var \Dandelion\Filesystem\FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var \Dandelion\VersionControl\GitInterface
     */
    protected $git;

    /**
     * @param \Dandelion\Configuration\ConfigurationLoaderInterface $configurationLoader
     * @param \Dandelion\Filesystem\FilesystemInterface $filesystem
     * @param \Dandelion\Process\ProcessPoolFactoryInterface $processPoolFactory
     * @param \Dandelion\Operation\ResultFactoryInterface $resultFactory
     * @param \Dandelion\Operation\Result\MessageFactoryInterface $messageFactory
     * @param \Dandelion\VersionControl\GitInterface $git
     */
    public function __construct(
        ConfigurationLoaderInterface $configurationLoader,
        FilesystemInterface $filesystem,
        ProcessPoolFactoryInterface $processPoolFactory,
        ResultFactoryInterface $resultFactory,
        MessageFactoryInterface $messageFactory,
        GitInterface $git
    ) {
        parent::__construct($configurationLoader, $processPoolFactory, $resultFactory, $messageFactory);
        $this->filesystem = $filesystem;
        $this->git = $git;
    }

    /**
     * @param string $repositoryName
     * @param string $branch
     *
     * @return \Dandelion\Operation\AbstractOperation
     *
     * @throws \Dandelion\Exception\RepositoryNotFoundException
     */
    public function executeForSingleRepository(
        string $repositoryName,
        string $branch
    ): AbstractOperation {
        $configuration = $this->configurationLoader->load();
        $repositories = $configuration->getRepositories();

        if (!$repositories->offsetExists($repositoryName)) {
            throw new RepositoryNotFoundException(sprintf('Could not find repository "%s".', $repositoryName));
        }

        $repository = $repositories->offsetGet($repositoryName);
        $tempDirectory = rtrim($configuration->getPathToTempDirectory(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . $repositoryName . DIRECTORY_SEPARATOR;

        $this->filesystem->createDirectory($tempDirectory);
        $currentWorkingDirectory = $this->filesystem->getCurrentWorkingDirectory();
        $this->filesystem->changeDirectory($tempDirectory);

        $this->doExecuteForSingleRepository($branch, $repository);

        $this->filesystem->changeDirectory($currentWorkingDirectory)
            ->removeDirectory($tempDirectory);

        return $this;
    }

    /**
     * @param string $branch
     * @param \Dandelion\Configuration\Repository $repository
     *
     * @return \Dandelion\Operation\AbstractOperation
     */
    protected function doExecuteForSingleRepository(string $branch, Repository $repository): AbstractOperation
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
     * @param string $repositoryName
     * @param string $branch
     *
     * @return string[]
     */
    protected function getCommand(string $repositoryName, string $branch): array
    {
        return [
            DANDELION_BINARY,
            ReleaseCommand::NAME,
            $repositoryName,
            $branch
        ];
    }
}
