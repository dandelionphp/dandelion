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
    protected $url;

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
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return \Dandelion\Configuration\Repository
     */
    public function setUrl(string $url): Repository
    {
        $this->url = $url;

        return $this;
    }
}
