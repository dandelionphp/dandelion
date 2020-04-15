<?php

declare(strict_types=1);

namespace Dandelion\Operation;

use Closure;
use Dandelion\Configuration\ConfigurationLoaderInterface;
use Dandelion\Operation\Result\MessageFactoryInterface;
use Dandelion\Operation\Result\MessageInterface;
use Dandelion\Process\ProcessPoolFactoryInterface;
use Symfony\Component\Process\Process;

abstract class AbstractOperation
{
    /**
     * @var \Dandelion\Configuration\ConfigurationLoaderInterface
     */
    protected $configurationLoader;

    /**
     * @var \Dandelion\Process\ProcessPoolFactoryInterface
     */
    protected $processPoolFactory;

    /**
     * @var \Dandelion\Operation\ResultFactoryInterface
     */
    protected $resultFactory;

    /**
     * @var \Dandelion\Operation\Result\MessageFactoryInterface
     */
    protected $messageFactory;

    /**
     * @var string
     */
    protected $binDir;

    /**
     * @param \Dandelion\Configuration\ConfigurationLoaderInterface $configurationLoader
     * @param \Dandelion\Process\ProcessPoolFactoryInterface $processPoolFactory
     * @param \Dandelion\Operation\ResultFactoryInterface $resultFactory
     * @param \Dandelion\Operation\Result\MessageFactoryInterface $messageFactory
     * @param string $binDir
     */
    public function __construct(
        ConfigurationLoaderInterface $configurationLoader,
        ProcessPoolFactoryInterface $processPoolFactory,
        ResultFactoryInterface $resultFactory,
        MessageFactoryInterface $messageFactory,
        string $binDir
    ) {
        $this->configurationLoader = $configurationLoader;
        $this->processPoolFactory = $processPoolFactory;
        $this->resultFactory = $resultFactory;
        $this->messageFactory = $messageFactory;
        $this->binDir = $binDir;
    }

    /**
     * @param string $repositoryName
     * @param string $branch
     *
     * @return \Dandelion\Operation\AbstractOperation
     */
    abstract public function executeForSingleRepository(string $repositoryName, string $branch): AbstractOperation;

    /**
     * @param string $branch
     *
     * @return \Dandelion\Operation\ResultInterface
     */
    public function executeForAllRepositories(string $branch = 'master'): ResultInterface
    {
        $configuration = $this->configurationLoader->load();
        $processPool = $this->processPoolFactory->create();
        $result = $this->resultFactory->create();

        foreach ($configuration->getRepositories() as $repositoryName => $repository) {
            $processPool->addProcess(
                $this->getCommand($repositoryName, $branch),
                $this->getCallback($result, $repositoryName)
            );
        }

        $processPool->start();

        return $result;
    }

    /**
     * @param \Dandelion\Operation\ResultInterface $result
     * @param string $repositoryName
     *
     * @return \Closure
     */
    protected function getCallback(ResultInterface $result, string $repositoryName): Closure
    {
        $messageFactory = $this->messageFactory;

        return static function (Process $process) use ($result, $messageFactory, $repositoryName) {
            // @codeCoverageIgnoreStart
            $type = $process->isSuccessful() ? MessageInterface::TYPE_INFO : MessageInterface::TYPE_ERROR;

            $message = $messageFactory->create()
                ->setType($type)
                ->setText($repositoryName);

            $result->addMessage($message);
            // @codeCoverageIgnoreEnd
        };
    }

    /**
     * @param string $repositoryName
     * @param string $branch
     *
     * @return string[]
     */
    abstract protected function getCommand(string $repositoryName, string $branch): array;
}
