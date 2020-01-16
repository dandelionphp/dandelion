<?php

namespace DR\Monorepo\Configuration;

class Repository implements RepositoryInterface
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
     * @return \DR\Monorepo\Configuration\RepositoryInterface
     */
    public function setPath(string $path): RepositoryInterface
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
     * @return \DR\Monorepo\Configuration\RepositoryInterface
     */
    public function setUrl(string $url): RepositoryInterface
    {
        $this->url = $url;

        return $this;
    }
}