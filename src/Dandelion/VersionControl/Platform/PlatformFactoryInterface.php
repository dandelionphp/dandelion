<?php

declare(strict_types=1);

namespace Dandelion\VersionControl\Platform;

use Dandelion\Configuration\Vcs;

interface PlatformFactoryInterface
{
    /**
     * @param \Dandelion\Configuration\Vcs $vcs
     *
     * @return \Dandelion\VersionControl\Platform\PlatformInterface
     */
    public function create(Vcs $vcs): PlatformInterface;
}
