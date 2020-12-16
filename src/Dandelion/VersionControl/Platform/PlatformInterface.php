<?php

declare(strict_types=1);

namespace Dandelion\VersionControl\Platform;

use Dandelion\Configuration\Repository;

interface PlatformInterface
{
    /**
     * @param \Dandelion\Configuration\Repository $repository
     *
     * @return string
     */
    public function getRepositoryUrl(Repository $repository): string;

    /**
     * @param \Dandelion\Configuration\Repository $repository
     *
     * @return \Dandelion\VersionControl\Platform\PlatformInterface
     */
    public function initSplitRepository(Repository $repository): PlatformInterface;

    /**
     * @param \Dandelion\Configuration\Repository $repository
     *
     * @return bool
     */
    public function existsSplitRepository(Repository $repository): bool;
}
