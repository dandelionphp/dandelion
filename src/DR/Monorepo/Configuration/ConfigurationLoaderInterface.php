<?php

namespace DR\Monorepo\Configuration;

interface ConfigurationLoaderInterface
{
    /**
     * @return \DR\Monorepo\Configuration\ConfigurationInterface
     */
    public function load(): ConfigurationInterface;
}
