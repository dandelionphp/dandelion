<?php

declare(strict_types=1);

namespace Dandelion\Operation;

interface ReleaserInterface
{
    /**
     * @param string $repositoryName
     * @param string $branch
     * @param string $version
     * @return \Dandelion\Operation\ReleaserInterface
     */
    public function release(
        string $repositoryName,
        string $branch,
        string $version
    ): ReleaserInterface;

    /**
     * @param string $branch
     * @param string $version
     *
     * @return \Dandelion\Operation\ReleaserInterface
     */
    public function releaseAll(string $branch, string $version): ReleaserInterface;
}
