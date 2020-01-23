<?php

declare(strict_types=1);

namespace Dandelion\Configuration;

use Codeception\Test\Unit;

class ConfigurationTest extends Unit
{
    /**
     * @var \Dandelion\Configuration\Configuration
     */
    protected $configuration;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Configuration\Repository
     */
    protected $repositoryMock;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->repositoryMock = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configuration = new Configuration();
    }

    /**
     * @return void
     */
    public function testSetAndGetRepositories(): void
    {
        $repositoryName = 'package';
        $repositories = [$repositoryName => $this->repositoryMock];

        $this->assertEquals($this->configuration, $this->configuration->setRepositories($repositories));
        $this->assertCount(1, $this->configuration->getRepositories());
        $this->assertTrue($this->configuration->getRepositories()->offsetExists($repositoryName));
        $this->assertEquals($this->repositoryMock, $this->configuration->getRepositories()->offsetGet($repositoryName));
    }

    /**
     * @return void
     */
    public function testSetAndGetPathToTempDirectory(): void
    {
        $pathToTempDirectory = '/temp';

        $this->assertEquals($this->configuration, $this->configuration->setPathToTempDirectory($pathToTempDirectory));
        $this->assertEquals($pathToTempDirectory, $this->configuration->getPathToTempDirectory());
    }
}