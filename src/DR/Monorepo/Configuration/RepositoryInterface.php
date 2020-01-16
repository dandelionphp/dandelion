<?php

namespace DR\Monorepo\Configuration;

interface RepositoryInterface
{
    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @param string $path
     *
     * @return \DR\Monorepo\Configuration\RepositoryInterface
     */
    public function setPath(string $path): RepositoryInterface;

    /**
     * @return string
     */
    public function getUrl(): string;

    /**
     * @param string $url
     *
     * @return \DR\Monorepo\Configuration\RepositoryInterface
     */
    public function setUrl(string $url): RepositoryInterface;
}