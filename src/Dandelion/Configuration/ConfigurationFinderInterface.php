<?php

declare(strict_types=1);

namespace Dandelion\Configuration;

use SplFileInfo;

interface ConfigurationFinderInterface
{
    /**
     * @return \SplFileInfo
     */
    public function find(): SplFileInfo;
}
