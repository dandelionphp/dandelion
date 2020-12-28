<?php

declare(strict_types=1);

namespace Dandelion\Operation;

use ArrayObject;
use Codeception\Test\Unit;
use Dandelion\Configuration\Configuration;
use Dandelion\Configuration\ConfigurationLoaderInterface;
use Dandelion\Configuration\Repository;
use Dandelion\Configuration\Vcs;
use Dandelion\Console\Command\SplitRepositoryInitCommand;
use Dandelion\Operation\Result\MessageFactoryInterface;
use Dandelion\Process\ProcessPoolFactoryInterface;
use Dandelion\Process\ProcessPoolInterface;
use Dandelion\VersionControl\Platform\PlatformFactoryInterface;
use Dandelion\VersionControl\Platform\PlatformInterface;
use Exception;
use Iterator;

class SplitRepositoryInitializerTest extends Unit
{
    /**
     * @var \Dandelion\Operation\SplitRepositoryInitializer
     */
    protected $splitRepositoryInitializer;

    /**
     * @var \Dandelion\Configuration\ConfigurationLoaderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configurationLoaderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Configuration\Configuration
     */
    protected $configurationMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\ArrayObject
     */
    protected $repositoriesMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Iterator
     */
    protected $iteratorMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Configuration\Repository
     */
    protected $repositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Configuration\Vcs
     */
    protected $vcsMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Process\ProcessPoolFactoryInterface
     */
    protected $processPoolFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Process\ProcessPoolInterface
     */
    protected $processPoolMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Operation\ResultFactoryInterface
     */
    protected $resultFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Operation\ResultInterface
     */
    protected $resultMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Operation\Result\MessageFactoryInterface
     */
    protected $messageFactoryMock;

    /**
     * @var \Dandelion\VersionControl\Platform\PlatformFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $platformFactoryMock;

    /**
     * @var \Dandelion\VersionControl\Platform\PlatformInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $platformMock;


    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->configurationLoaderMock = $this->getMockBuilder(ConfigurationLoaderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurationMock = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->repositoriesMock = $this->getMockBuilder(ArrayObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->iteratorMock = $this->getMockBuilder(Iterator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->repositoryMock = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->vcsMock = $this->getMockBuilder(Vcs::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->processPoolFactoryMock = $this->getMockBuilder(ProcessPoolFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->processPoolMock = $this->getMockBuilder(ProcessPoolInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock = $this->getMockBuilder(ResultFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultMock = $this->getMockBuilder(ResultInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageFactoryMock = $this->getMockBuilder(MessageFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->platformFactoryMock  = $this->getMockBuilder(PlatformFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->platformMock  = $this->getMockBuilder(PlatformInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->splitRepositoryInitializer = new SplitRepositoryInitializer(
            $this->configurationLoaderMock,
            $this->processPoolFactoryMock,
            $this->resultFactoryMock,
            $this->messageFactoryMock,
            $this->platformFactoryMock
        );
    }

    /**
     * @throws \Exception
     *
     * @return void
     */
    public function testExecuteForSingleRepository(): void
    {
        $repositoryName = 'package';

        $this->configurationLoaderMock->expects(static::atLeastOnce())
            ->method('load')
            ->willReturn($this->configurationMock);

        $this->configurationMock->expects(static::atLeastOnce())
            ->method('getRepositories')
            ->willReturn($this->repositoriesMock);

        $this->repositoriesMock->expects(static::atLeastOnce())
            ->method('offsetExists')
            ->with($repositoryName)
            ->willReturn(true);

        $this->repositoriesMock->expects(static::atLeastOnce())
            ->method('offsetGet')
            ->with($repositoryName)
            ->willReturn($this->repositoryMock);

        $this->configurationMock->expects(static::atLeastOnce())
            ->method('getVcs')
            ->willReturn($this->vcsMock);

        $this->platformFactoryMock->expects(static::atLeastOnce())
            ->method('create')
            ->with($this->vcsMock)
            ->willReturn($this->platformMock);

        $this->platformMock->expects(static::atLeastOnce())
            ->method('existsSplitRepository')
            ->with($this->repositoryMock)
            ->willReturn(false);

        $this->platformMock->expects(static::atLeastOnce())
            ->method('initSplitRepository')
            ->with($this->repositoryMock)
            ->willReturn($this->platformMock);

        static::assertEquals(
            $this->splitRepositoryInitializer,
            $this->splitRepositoryInitializer->executeForSingleRepository($repositoryName)
        );
    }

    /**
     * @throws \Exception
     *
     * @return void
     */
    public function testExecuteForSingleRepositoryWithExistingSplitRepository(): void
    {
        $repositoryName = 'package';

        $this->configurationLoaderMock->expects(static::atLeastOnce())
            ->method('load')
            ->willReturn($this->configurationMock);

        $this->configurationMock->expects(static::atLeastOnce())
            ->method('getRepositories')
            ->willReturn($this->repositoriesMock);

        $this->repositoriesMock->expects(static::atLeastOnce())
            ->method('offsetExists')
            ->with($repositoryName)
            ->willReturn(true);

        $this->repositoriesMock->expects(static::atLeastOnce())
            ->method('offsetGet')
            ->with($repositoryName)
            ->willReturn($this->repositoryMock);

        $this->configurationMock->expects(static::atLeastOnce())
            ->method('getVcs')
            ->willReturn($this->vcsMock);

        $this->platformFactoryMock->expects(static::atLeastOnce())
            ->method('create')
            ->with($this->vcsMock)
            ->willReturn($this->platformMock);

        $this->platformMock->expects(static::atLeastOnce())
            ->method('existsSplitRepository')
            ->with($this->repositoryMock)
            ->willReturn(true);

        $this->platformMock->expects(static::never())
            ->method('initSplitRepository');

        static::assertEquals(
            $this->splitRepositoryInitializer,
            $this->splitRepositoryInitializer->executeForSingleRepository($repositoryName)
        );
    }

    /**
     * @throws \Exception
     *
     * @return void
     */
    public function testExecuteForSingleRepositoriesWithNonExistingRepository(): void
    {
        $repositoryName = 'package';

        $this->configurationLoaderMock->expects(static::atLeastOnce())
            ->method('load')
            ->willReturn($this->configurationMock);

        $this->configurationMock->expects(static::atLeastOnce())
            ->method('getRepositories')
            ->willReturn($this->repositoriesMock);

        $this->repositoriesMock->expects(static::atLeastOnce())
            ->method('offsetExists')
            ->with($repositoryName)
            ->willReturn(false);

        $this->repositoriesMock->expects(static::never())
            ->method('offsetGet');

        $this->configurationMock->expects(static::never())
            ->method('getVcs');

        $this->platformFactoryMock->expects(static::never())
            ->method('create');

        try {
            $this->splitRepositoryInitializer->executeForSingleRepository($repositoryName);
        } catch (Exception $e) {
            return;
        }

        static::fail();
    }

    /**
     * @return void
     */
    public function testExecuteForAllRepositories(): void
    {
        $repositoryName = 'package';

        $this->configurationLoaderMock->expects(static::atLeastOnce())
            ->method('load')
            ->willReturn($this->configurationMock);

        $this->processPoolFactoryMock->expects(static::atLeastOnce())
            ->method('create')
            ->willReturn($this->processPoolMock);

        $this->resultFactoryMock->expects(static::atLeastOnce())
            ->method('create')
            ->willReturn($this->resultMock);

        $this->configurationMock->expects(static::atLeastOnce())
            ->method('getRepositories')
            ->willReturn($this->repositoriesMock);

        $this->repositoriesMock->expects(static::atLeastOnce())
            ->method('getIterator')
            ->willReturn($this->iteratorMock);

        $this->iteratorMock->expects(static::atLeastOnce())
            ->method('rewind');

        $this->iteratorMock->expects(static::atLeastOnce())
            ->method('valid')
            ->willReturnOnConsecutiveCalls(true, false);

        $this->iteratorMock->expects(static::atLeastOnce())
            ->method('current')
            ->willReturn($this->repositoryMock);

        $this->iteratorMock->expects(static::atLeastOnce())
            ->method('key')
            ->willReturn($repositoryName);

        $this->processPoolMock->expects(static::atLeastOnce())
            ->method('addProcess')
            ->with([
                DANDELION_BINARY,
                SplitRepositoryInitCommand::NAME,
                $repositoryName
            ])->willReturn($this->processPoolMock);

        $this->processPoolMock->expects(static::atLeastOnce())
            ->method('start')
            ->willReturn($this->processPoolMock);

        static::assertEquals(
            $this->resultMock,
            $this->splitRepositoryInitializer->executeForAllRepositories([])
        );
    }
}
