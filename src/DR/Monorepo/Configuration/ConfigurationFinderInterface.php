<?php

namespace DR\Monorepo\Configuration;

use SplFileInfo;

interface ConfigurationFinderInterface
{
    /**
     * @return \SplFileInfo
     */
    public function find(): SplFileInfo;
}
