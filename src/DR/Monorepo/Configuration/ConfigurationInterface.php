<?php

declare(strict_types=1);

namespace DR\Monorepo\Configuration;

interface ConfigurationInterface
{
    /**
     * @return string[]
     */
    public function getRepositories(): array;

    /**
     * @param string[] $repositories
     *
     * @return \DR\Monorepo\Configuration\ConfigurationInterface
     */
    public function setRepositories(array $repositories): ConfigurationInterface;
}
