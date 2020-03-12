<?php

declare(strict_types=1);

namespace Dandelion\Operation;

interface ReleaserInterface
{
    /**
     * @param string $repositoryName
     * @param string $branch
     *
     * @return \Dandelion\Operation\ReleaserInterface
     */
    public function release(
        string $repositoryName,
        string $branch
    ): ReleaserInterface;

    /**
     * @param string $branch
     *
     * @return \Dandelion\Operation\ReleaserInterface
     */
    public function releaseAll(string $branch): ReleaserInterface;
}
