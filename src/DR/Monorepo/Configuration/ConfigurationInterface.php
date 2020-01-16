<?php

declare(strict_types=1);

namespace DR\Monorepo\Configuration;

use ArrayObject;

interface ConfigurationInterface
{
    /**
     * @return \ArrayObject<string,\DR\Monorepo\Configuration\RepositoryInterface>
     */
    public function getRepositories(): ArrayObject;

    /**
     * @param \DR\Monorepo\Configuration\RepositoryInterface[] $repositories
     *
     * @return \DR\Monorepo\Configuration\ConfigurationInterface
     */
    public function setRepositories(array $repositories): ConfigurationInterface;

    /**
     * @return string
     */
    public function getPathToTempDirectory(): string;

    /**
     * @param string $pathToTempDirectory
     *
     * @return \DR\Monorepo\Configuration\ConfigurationInterface
     */
    public function setPathToTempDirectory(string $pathToTempDirectory): ConfigurationInterface;
}
