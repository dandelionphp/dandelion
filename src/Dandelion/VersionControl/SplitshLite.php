<?php

declare(strict_types=1);

namespace Dandelion\VersionControl;

use Dandelion\Configuration\ConfigurationLoaderInterface;
use Dandelion\Exception\RuntimeException;
use Dandelion\Process\ProcessFactory;

use function sprintf;

class SplitshLite implements SplitshLiteInterface
{
    public const DEFAULT_PATH_TO_BINARY = '/usr/local/bin/splitsh-lite';

    /**
     * @var \Dandelion\Process\ProcessFactory
     */
    protected $processFactory;

    /**
     * @var \Dandelion\Configuration\ConfigurationLoaderInterface
     */
    protected $configurationLoader;

    /**
     * @param \Dandelion\Process\ProcessFactory $processFactory
     * @param \Dandelion\Configuration\ConfigurationLoaderInterface $configurationLoader
     */
    public function __construct(
        ProcessFactory $processFactory,
        ConfigurationLoaderInterface $configurationLoader
    ) {
        $this->processFactory = $processFactory;
        $this->configurationLoader = $configurationLoader;
    }

    /**
     * @param string $pathToPackage
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getSha1(string $pathToPackage): string
    {
        $command = [
            $this->getPathToBinary(),
            sprintf('--prefix=%s', $pathToPackage),
            '--quiet'
        ];

        $process = $this->processFactory->create($command);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getExitCodeText(), $process->getExitCode());
        }

        return trim($process->getOutput());
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    protected function getPathToBinary(): string
    {
        $configuration = $this->configurationLoader->load();

        return $configuration->getPathToSplitshLite() ?? static::DEFAULT_PATH_TO_BINARY;
    }
}
