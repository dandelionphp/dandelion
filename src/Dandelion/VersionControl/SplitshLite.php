<?php

declare(strict_types=1);

namespace Dandelion\VersionControl;

use Dandelion\Environment\OperatingSystem;
use Dandelion\Environment\OperatingSystemInterface;
use Dandelion\Exception\RuntimeException;
use Dandelion\Process\ProcessFactory;

use function sprintf;
use function strtolower;

class SplitshLite implements SplitshLiteInterface
{
    protected const BINARY_NAME_SPLITSH_LITE = 'splitsh-lite';

    /**
     * @var \Dandelion\Environment\OperatingSystemInterface
     */
    protected $operatingSystem;

    /**
     * @var \Dandelion\Process\ProcessFactory
     */
    protected $processFactory;

    /**
     * @var string
     */
    protected $pathToBinDirectory;

    /**
     * @param \Dandelion\Environment\OperatingSystemInterface $operatingSystem
     * @param \Dandelion\Process\ProcessFactory $processFactory
     * @param string $pathToBinDirectory
     */
    public function __construct(
        OperatingSystemInterface $operatingSystem,
        ProcessFactory $processFactory,
        string $pathToBinDirectory
    ) {
        $this->processFactory = $processFactory;
        $this->pathToBinDirectory = $pathToBinDirectory;
        $this->operatingSystem = $operatingSystem;
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
            $this->getPathToSplitshLiteBin(),
            sprintf('--prefix=%s', $pathToPackage)
        ];

        $process = $this->processFactory->create($command);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getExitCodeText(), $process->getExitCode());
        }

        return $process->getOutput();
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    protected function getPathToSplitshLiteBin(): string
    {
        $machineType = $this->operatingSystem->getMachineType();

        if ($machineType !== OperatingSystem::MACHINE_TYPE_X86_64) {
            throw new RuntimeException(sprintf('Machine type "%s" is not supported', $machineType));
        }

        $osFamily = $this->operatingSystem->getFamily();

        if ($osFamily !== OperatingSystem::FAMILY_DARWIN && $osFamily !== OperatingSystem::FAMILY_LINUX) {
            throw new RuntimeException(sprintf('OS family "%s" is not supported', $osFamily));
        }

        return sprintf(
            '%s%s-%s',
            $this->pathToBinDirectory,
            static::BINARY_NAME_SPLITSH_LITE,
            strtolower($osFamily)
        );
    }
}
