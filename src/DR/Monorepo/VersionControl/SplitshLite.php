<?php

namespace DR\Monorepo\VersionControl;

use DR\Monorepo\Environment\OperatingSystem;
use DR\Monorepo\Environment\OperatingSystemInterface;
use DR\Monorepo\Exception\RuntimeException;
use DR\Monorepo\Process\ProcessFactory;
use function sprintf;
use function strtolower;

class SplitshLite implements SplitshLiteInterface
{
    protected const BINARY_NAME_SPLITSH_LITE = 'splitsh-lite';

    /**
     * @var \DR\Monorepo\Environment\OperatingSystemInterface
     */
    protected $operatingSystem;

    /**
     * @var \DR\Monorepo\Process\ProcessFactory
     */
    protected $processFactory;

    /**
     * @var string
     */
    protected $pathToBinDirectory;

    /**
     * @param \DR\Monorepo\Environment\OperatingSystemInterface $operatingSystem
     * @param \DR\Monorepo\Process\ProcessFactory $processFactory
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