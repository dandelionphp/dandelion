<?php

namespace DR\Monorepo\Configuration;

use Codeception\Test\Unit;

class ConfigurationTest extends Unit
{
    /**
     * @var \DR\Monorepo\Configuration\Configuration
     */
    protected $configuration;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->configuration = new Configuration();
    }

    /**
     * @return void
     */
    public function testSetAndGetRepositories(): void
    {
        $repositories = ['/path/to/package' => 'git@github.com:user/package.git'];

        $this->assertEquals($this->configuration, $this->configuration->setRepositories($repositories));
        $this->assertEquals($repositories, $this->configuration->getRepositories());
    }
}