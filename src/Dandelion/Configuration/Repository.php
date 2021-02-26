<?php

declare(strict_types=1);

namespace Dandelion\Configuration;

class Repository
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $version;

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     *
     * @return \Dandelion\Configuration\Repository
     */
    public function setPath(string $path): Repository
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     *
     * @return \Dandelion\Configuration\Repository
     */
    public function setVersion(string $version): Repository
    {
        $this->version = $version;

        return $this;
    }
}
