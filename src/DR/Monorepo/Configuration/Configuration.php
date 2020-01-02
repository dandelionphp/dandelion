<?php

declare(strict_types=1);

namespace DR\Monorepo\Configuration;

class Configuration implements ConfigurationInterface
{
    /**
     * @var string[]
     */
    protected $repositories;

    /**
     * @return string[]
     */
    public function getRepositories(): array
    {
        return $this->repositories;
    }

    /**
     * @param string[] $repositories
     *
     * @return \DR\Monorepo\Configuration\ConfigurationInterface
     */
    public function setRepositories(array $repositories): ConfigurationInterface
    {
        $this->repositories = $repositories;

        return $this;
    }
}
