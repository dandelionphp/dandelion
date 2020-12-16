<?php

declare(strict_types=1);

namespace Dandelion\Operation;

use Closure;
use Dandelion\Configuration\ConfigurationLoaderInterface;
use Dandelion\Operation\Result\MessageFactoryInterface;
use Dandelion\Operation\Result\MessageInterface;
use Dandelion\Process\ProcessPoolFactoryInterface;
use Dandelion\VersionControl\Platform\PlatformFactoryInterface;
use Symfony\Component\Process\Process;

use function array_merge;

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
     * @var \Dandelion\VersionControl\Platform\PlatformFactoryInterface
     */
    protected $platformFactory;

    /**
     * @param \Dandelion\Configuration\ConfigurationLoaderInterface $configurationLoader
     * @param \Dandelion\Process\ProcessPoolFactoryInterface $processPoolFactory
     * @param \Dandelion\Operation\ResultFactoryInterface $resultFactory
     * @param \Dandelion\Operation\Result\MessageFactoryInterface $messageFactory
     * @param \Dandelion\VersionControl\Platform\PlatformFactoryInterface $platformFactory
     */
    public function __construct(
        ConfigurationLoaderInterface $configurationLoader,
        ProcessPoolFactoryInterface $processPoolFactory,
        ResultFactoryInterface $resultFactory,
        MessageFactoryInterface $messageFactory,
        PlatformFactoryInterface $platformFactory
    ) {
        $this->configurationLoader = $configurationLoader;
        $this->processPoolFactory = $processPoolFactory;
        $this->resultFactory = $resultFactory;
        $this->messageFactory = $messageFactory;
        $this->platformFactory = $platformFactory;
    }

    /**
     * @param string[] $arguments
     *
     * @return \Dandelion\Operation\ResultInterface
     */
    public function executeForAllRepositories(array $arguments = []): ResultInterface
    {
        $configuration = $this->configurationLoader->load();
        $processPool = $this->processPoolFactory->create();
        $result = $this->resultFactory->create();

        foreach ($configuration->getRepositories() as $repositoryName => $repository) {
            $commandArguments = array_merge([$repositoryName], $arguments);

            $processPool->addProcess(
                $this->getCommand($commandArguments),
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
     * @param string[] $commandArguments
     *
     * @return string[]
     */
    abstract protected function getCommand(array $commandArguments): array;
}
