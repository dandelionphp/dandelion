<?php

declare(strict_types=1);

namespace Dandelion\Configuration;

use ArrayObject;

class Configuration
{
    /**
     * @var \ArrayObject<string,\Dandelion\Configuration\Repository>
     */
    protected $repositories;

    /**
     * @var string
     */
    protected $pathToTempDirectory;

    /**
     * @var string|null
     */
    protected $pathToSplitshLite;

    /**
     * @return \ArrayObject<string,\Dandelion\Configuration\Repository>
     */
    public function getRepositories(): ArrayObject
    {
        return $this->repositories;
    }

    /**
     * @param \Dandelion\Configuration\Repository[] $repositories
     *
     * @return \Dandelion\Configuration\Configuration
     */
    public function setRepositories(array $repositories): Configuration
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
     * @return \Dandelion\Configuration\Configuration
     */
    public function setPathToTempDirectory(string $pathToTempDirectory): Configuration
    {
        $this->pathToTempDirectory = $pathToTempDirectory;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPathToSplitshLite(): ?string
    {
        return $this->pathToSplitshLite;
    }

    /**
     * @param string|null $pathToSplitshLite
     *
     * @return \Dandelion\Configuration\Configuration
     */
    public function setPathToSplitshLite(?string $pathToSplitshLite): Configuration
    {
        $this->pathToSplitshLite = $pathToSplitshLite;

        return $this;
    }
}
