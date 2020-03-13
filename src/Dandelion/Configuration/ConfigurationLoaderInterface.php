<?php

declare(strict_types=1);

namespace Dandelion\Configuration;

interface ConfigurationLoaderInterface
{
    /**
     * @return \Dandelion\Configuration\Configuration
     */
    public function load(): Configuration;

    /**
     * @return string
     */
    public function loadRaw(): string;
}
