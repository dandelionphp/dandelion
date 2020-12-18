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
use Dandelion\VersionControl\Platform\PlatformFactoryInterface;
use Dandelion\VersionControl\Platform\PlatformInterface;

use function sprintf;

class Releaser extends AbstractOperation implements ReleaserInterface
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
     * @param \Dandelion\VersionControl\Platform\PlatformFactoryInterface $platformFactory
     * @param \Dandelion\VersionControl\GitInterface $git
     */
    public function __construct(
        ConfigurationLoaderInterface $configurationLoader,
        FilesystemInterface $filesystem,
        ProcessPoolFactoryInterface $processPoolFactory,
        ResultFactoryInterface $resultFactory,
        MessageFactoryInterface $messageFactory,
        PlatformFactoryInterface $platformFactory,
        GitInterface $git
    ) {
        parent::__construct(
            $configurationLoader,
            $processPoolFactory,
            $resultFactory,
            $messageFactory,
            $platformFactory
        );
        $this->filesystem = $filesystem;
        $this->git = $git;
    }

    /**
     * @param string $repositoryName
     * @param string $branch
     *
     * @return \Dandelion\Operation\ReleaserInterface
     *
     * @throws \Dandelion\Exception\RepositoryNotFoundException
     */
    public function executeForSingleRepository(
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

        $this->filesystem->createDirectory($tempDirectory);
        $currentWorkingDirectory = $this->filesystem->getCurrentWorkingDirectory();
        $this->filesystem->changeDirectory($tempDirectory);

        $platform = $this->platformFactory->create($configuration->getVcs());

        $this->doExecuteForSingleRepository($platform, $branch, $repository);

        $this->filesystem->changeDirectory($currentWorkingDirectory)
            ->removeDirectory($tempDirectory);

        return $this;
    }

    /**
     * @param \Dandelion\VersionControl\Platform\PlatformInterface $platform
     * @param string $branch
     * @param \Dandelion\Configuration\Repository $repository
     *
     * @return \Dandelion\Operation\AbstractOperation
     */
    protected function doExecuteForSingleRepository(
        PlatformInterface $platform,
        string $branch,
        Repository $repository
    ): AbstractOperation {
        $version = $repository->getVersion();

        $this->git->clone($platform->getRepositoryUrl($repository), '.')
            ->checkout($branch);

        if ($this->git->describeClosestTag($version) !== null) {
            return $this;
        }

        $this->git->tag($version)
            ->pushWithTags('origin');

        return $this;
    }

    /**
     * @param string[] $commandArguments
     *
     * @return string[]
     */
    protected function getCommand(array $commandArguments): array
    {
        return array_merge(
            [
                DANDELION_BINARY,
                ReleaseCommand::NAME,
            ],
            $commandArguments
        );
    }
}
