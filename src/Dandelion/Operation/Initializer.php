<?php

namespace Dandelion\Operation;

use Dandelion\Configuration\ConfigurationLoaderInterface;
use Dandelion\Operation\Result\MessageFactoryInterface;
use Dandelion\Operation\Result\MessageInterface;
use Dandelion\VersionControl\GitInterface;

class Initializer implements InitializerInterface
{
    /**
     * @var \Dandelion\VersionControl\GitInterface
     */
    private $git;
    /**
     * @var \Dandelion\Configuration\ConfigurationLoaderInterface
     */
    private $configurationLoader;
    /**
     * @var \Dandelion\Operation\Result\MessageFactoryInterface
     */
    private $messageFactory;
    /**
     * @var \Dandelion\Operation\ResultFactoryInterface
     */
    private $resultFactory;

    /**
     * @param \Dandelion\VersionControl\GitInterface $git
     * @param \Dandelion\Configuration\ConfigurationLoaderInterface $configurationLoader
     * @param \Dandelion\Operation\Result\MessageFactoryInterface $messageFactory
     * @param \Dandelion\Operation\ResultFactoryInterface $resultFactory
     */
    public function __construct(
        GitInterface $git,
        ConfigurationLoaderInterface $configurationLoader,
        MessageFactoryInterface $messageFactory,
        ResultFactoryInterface $resultFactory
    ) {
        $this->git = $git;
        $this->configurationLoader = $configurationLoader;
        $this->messageFactory = $messageFactory;
        $this->resultFactory = $resultFactory;
    }

    /**
     * @return \Dandelion\Operation\ResultInterface
     */
    public function addGitRemotes(): ResultInterface
    {
        $configuration = $this->configurationLoader->load();
        $repositories = $configuration->getRepositories();
        $result = $this->resultFactory->create();

        foreach ($repositories as $repositoryName => $repository) {
            if ($this->git->existsRemote($repositoryName)) {
                $message = $this->messageFactory->create()
                    ->setType(MessageInterface::TYPE_INFO)
                    ->setText(sprintf('%s already exists, skip.', $repositoryName));

                $result->addMessage($message);
                continue;
            }

            $success = $this->git->addRemote($repositoryName, $repository->getUrl());
            $type = $success ? MessageInterface::TYPE_INFO : MessageInterface::TYPE_ERROR;
            $message = $this->messageFactory->create()
                ->setType($type)
                ->setText($repositoryName);

            $result->addMessage($message);
        }

        return $result;
    }
}
