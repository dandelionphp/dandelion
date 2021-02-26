<?php

declare(strict_types=1);

namespace Dandelion\VersionControl\Platform;

interface PlatformInterface
{
    /**
     * @param string $repositoryName
     *
     * @return string
     */
    public function getRepositoryUrl(string $repositoryName): string;

    /**
     * @param string $repositoryName
     *
     * @return \Dandelion\VersionControl\Platform\PlatformInterface
     */
    public function initSplitRepository(string $repositoryName): PlatformInterface;

    /**
     * @param string $repositoryName
     *
     * @return bool
     */
    public function existsSplitRepository(string $repositoryName): bool;
}
