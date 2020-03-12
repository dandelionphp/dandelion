<?php

declare(strict_types=1);

namespace Dandelion\Operation;

use Dandelion\Configuration\ConfigurationLoaderInterface;
use Dandelion\Console\Command\SplitCommand;
use Dandelion\Exception\RepositoryNotFoundException;
use Dandelion\Process\ProcessFactory;
use Dandelion\VersionControl\GitInterface;
use Dandelion\VersionControl\SplitshLiteInterface;

use function sprintf;

class Splitter implements SplitterInterface
{
    /**
     * @var \Dandelion\Configuration\ConfigurationLoaderInterface
     */
    protected $configurationLoader;

    /**
     * @var \Dandelion\Process\ProcessFactory
     */
    protected $processFactory;

    /**
     * @var \Dandelion\VersionControl\GitInterface
     */
    protected $git;

    /**
     * @var \Dandelion\VersionControl\SplitshLiteInterface
     */
    protected $splitshLite;

    /**
     * @var string
     */
    protected $binDir;

    /**
     * @param \Dandelion\Configuration\ConfigurationLoaderInterface $configurationLoader
     * @param \Dandelion\Process\ProcessFactory $processFactory
     * @param \Dandelion\VersionControl\GitInterface $git
     * @param \Dandelion\VersionControl\SplitshLiteInterface $splitshLite
     * @param string $binDir
     */
    public function __construct(
        ConfigurationLoaderInterface $configurationLoader,
        ProcessFactory $processFactory,
        GitInterface $git,
        SplitshLiteInterface $splitshLite,
        string $binDir
    ) {
        $this->configurationLoader = $configurationLoader;
        $this->processFactory = $processFactory;
        $this->git = $git;
        $this->splitshLite = $splitshLite;
        $this->binDir = $binDir;
    }

    /**
     * @param string $repositoryName
     * @param string $branch
     *
     * @return \Dandelion\Operation\SplitterInterface
     *
     * @throws \Exception
     */
    public function split(string $repositoryName, string $branch): SplitterInterface
    {
        $configuration = $this->configurationLoader->load();
        $repositories = $configuration->getRepositories();

        if (!$repositories->offsetExists($repositoryName)) {
            throw new RepositoryNotFoundException(\sprintf('Could not find repository "%s".', $repositoryName));
        }

        $repository = $repositories->offsetGet($repositoryName);

        if (!$this->git->existsRemote($repositoryName)) {
            $this->git->addRemote($repositoryName, $repository->getUrl());
        }

        $sha1 = $this->splitshLite->getSha1($repository->getPath());
        $refSpec = sprintf('%s:refs/heads/%s', $sha1, $branch);

        $this->git->pushForcefully($repositoryName, $refSpec);

        return $this;
    }

    /**
     * @param string $branch
     *
     * @return \Dandelion\Operation\SplitterInterface
     */
    public function splitAll(string $branch = 'master'): SplitterInterface
    {
        $configuration = $this->configurationLoader->load();

        foreach ($configuration->getRepositories() as $repositoryName => $repository) {
            $this->splitAsProcess($repositoryName, $branch);
        }

        return $this;
    }

    /**
     * @param string $repositoryName
     * @param string $branch
     *
     * @return \Dandelion\Operation\SplitterInterface
     */
    protected function splitAsProcess(
        string $repositoryName,
        string $branch
    ): SplitterInterface {
        $command = $command = [
            sprintf('%sdandelion', $this->binDir),
            SplitCommand::NAME,
            $repositoryName,
            $branch
        ];

        $process = $this->processFactory->create($command);

        $process->start();

        return $this;
    }
}
