<?php

namespace DR\Monorepo\VersionControl;

use DR\Monorepo\Process\ProcessFactory;
use Exception;
use RuntimeException;
use function sprintf;
use function strtolower;

class SplitshLite implements SplitshLiteInterface
{
    protected const PHP_OS_FAMILY_DARWIN = 'Darwin';
    protected const PHP_OS_FAMILY_LINUX = 'Linux';

    protected const MACHINE_TYPE_X86_64 = 'x86_64';

    protected const PATH_TO_SPLITSH_LITE = __DIR__ . '/../../../../bin/splitsh-lite';

    /**
     * @var \DR\Monorepo\Process\ProcessFactory
     */
    protected $processFactory;

    /**
     * @param \DR\Monorepo\Process\ProcessFactory $processFactory
     */
    public function __construct(
        ProcessFactory $processFactory
    ) {
        $this->processFactory = $processFactory;
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
        $machineType = php_uname('m');

        if ($machineType !== static::MACHINE_TYPE_X86_64) {
            throw new Exception(sprintf('Machine type "%s" is not supported', $machineType));
        }

        if (PHP_OS_FAMILY !== static::PHP_OS_FAMILY_DARWIN && PHP_OS_FAMILY !== static::PHP_OS_FAMILY_LINUX) {
            throw new Exception(sprintf('OS family "%s" is not supported', PHP_OS_FAMILY));
        }

        return sprintf('%s-%s', static::PATH_TO_SPLITSH_LITE, strtolower(PHP_OS_FAMILY));
    }
}