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
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Configuration\Vcs
     */
    protected $vcsMock;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->repositoryMock = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->vcsMock = $this->getMockBuilder(Vcs::class)
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

        static::assertEquals($this->configuration, $this->configuration->setRepositories($repositories));
        static::assertCount(1, $this->configuration->getRepositories());
        static::assertTrue($this->configuration->getRepositories()->offsetExists($repositoryName));
        static::assertEquals($this->repositoryMock, $this->configuration->getRepositories()->offsetGet($repositoryName));
    }

    /**
     * @return void
     */
    public function testSetAndGetVcs(): void
    {
        static::assertEquals($this->configuration, $this->configuration->setVcs($this->vcsMock));
        static::assertEquals($this->vcsMock, $this->configuration->getVcs());
    }

    /**
     * @return void
     */
    public function testSetAndGetPathToTempDirectory(): void
    {
        $pathToTempDirectory = '/temp';

        static::assertEquals($this->configuration, $this->configuration->setPathToTempDirectory($pathToTempDirectory));
        static::assertEquals($pathToTempDirectory, $this->configuration->getPathToTempDirectory());
    }

    /**
     * @return void
     */
    public function testGetPathToSplitshLite(): void
    {
        static::assertEquals(null, $this->configuration->getPathToSplitshLite());
    }

    /**
     * @return void
     */
    public function testSetAndGetPathToSplitshLite(): void
    {
        $pathToSplitshLite = '/usr/local/bin/splitsh-lite';

        static::assertEquals($this->configuration, $this->configuration->setPathToSplitshLite($pathToSplitshLite));
        static::assertEquals($pathToSplitshLite, $this->configuration->getPathToSplitshLite());
    }
}
