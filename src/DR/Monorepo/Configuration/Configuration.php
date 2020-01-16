<?php

declare(strict_types=1);

namespace DR\Monorepo\Configuration;

use ArrayObject;

class Configuration implements ConfigurationInterface
{
    /**
     * @var \ArrayObject<string,\DR\Monorepo\Configuration\Repository>
     */
    protected $repositories;

    /**
     * @var string
     */
    protected $pathToTempDirectory;

    /**
     * @return \ArrayObject<string,\DR\Monorepo\Configuration\Repository>
     */
    public function getRepositories(): ArrayObject
    {
        return $this->repositories;
    }

    /**
     * @param \DR\Monorepo\Configuration\Repository[] $repositories
     *
     * @return \DR\Monorepo\Configuration\ConfigurationInterface
     */
    public function setRepositories(array $repositories): ConfigurationInterface
    {
        $this->repositories = new ArrayObject($repositories);

        return $this;
    }

    /**
     * @return string
     */
    public function getPathToTempDirectory(): string
    {
        return $this->pathToTempDirectory;
    }

    /**
     * @param string $pathToTempDirectory
     *
     * @return \DR\Monorepo\Configuration\ConfigurationInterface
     */
    public function setPathToTempDirectory(string $pathToTempDirectory): ConfigurationInterface
    {
        $this->pathToTempDirectory = $pathToTempDirectory;

        return $this;
    }
}
