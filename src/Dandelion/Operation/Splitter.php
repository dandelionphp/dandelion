<?php

declare(strict_types=1);

namespace Dandelion\Operation;

use Dandelion\Configuration\ConfigurationLoaderInterface;
use Dandelion\Console\Command\SplitCommand;
use Dandelion\Exception\RepositoryNotFoundException;
use Dandelion\Lock\LockTrait;
use Dandelion\Operation\Result\MessageFactoryInterface;
use Dandelion\Process\ProcessPoolFactoryInterface;
use Dandelion\VersionControl\GitInterface;
use Dandelion\VersionControl\SplitshLiteInterface;
use Symfony\Component\Lock\LockFactory;

use function sprintf;

class Splitter extends AbstractOperation
{
    use LockTrait;

    public const LOCK_IDENTIFIER = 'LOCK_SPLIT';

    /**
     * @var \Dandelion\VersionControl\GitInterface
     */
    protected $git;

    /**
     * @var \Dandelion\VersionControl\SplitshLiteInterface
     */
    protected $splitshLite;

    /**
     * @param \Dandelion\Configuration\ConfigurationLoaderInterface $configurationLoader
     * @param \Dandelion\Process\ProcessPoolFactoryInterface $processPoolFactory
     * @param \Dandelion\Operation\ResultFactoryInterface $resultFactory
     * @param \Dandelion\Operation\Result\MessageFactoryInterface $messageFactory
     * @param \Dandelion\VersionControl\GitInterface $git
     * @param \Dandelion\VersionControl\SplitshLiteInterface $splitshLite
     * @param \Symfony\Component\Lock\LockFactory $lockFactory
     */
    public function __construct(
        ConfigurationLoaderInterface $configurationLoader,
        ProcessPoolFactoryInterface $processPoolFactory,
        ResultFactoryInterface $resultFactory,
        MessageFactoryInterface $messageFactory,
        GitInterface $git,
        SplitshLiteInterface $splitshLite,
        LockFactory $lockFactory
    ) {
        parent::__construct($configurationLoader, $processPoolFactory, $resultFactory, $messageFactory);

        $this->git = $git;
        $this->splitshLite = $splitshLite;
        $this->lockFactory = $lockFactory;
    }

    /**
     * @param string $repositoryName
     * @param string $branch
     *
     * @return \Dandelion\Operation\AbstractOperation
     *
     * @throws \Dandelion\Exception\RepositoryNotFoundException
     */
    public function executeForSingleRepository(string $repositoryName, string $branch): AbstractOperation
    {
        $configuration = $this->configurationLoader->load();
        $repositories = $configuration->getRepositories();

        if (!$repositories->offsetExists($repositoryName)) {
            throw new RepositoryNotFoundException(\sprintf('Could not find repository "%s".', $repositoryName));
        }

        $repository = $repositories->offsetGet($repositoryName);

        $this->acquire(static::LOCK_IDENTIFIER);

        if (!$this->git->existsRemote($repositoryName)) {
            $this->git->addRemote($repositoryName, $repository->getUrl());
        }

        $this->release();

        $sha1 = $this->splitshLite->getSha1($repository->getPath());
        $refSpec = sprintf('%s:refs/heads/%s', $sha1, $branch);

        $this->git->pushForcefully($repositoryName, $refSpec);

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
            SplitCommand::NAME,
            $repositoryName,
            $branch
        ];
    }
}
