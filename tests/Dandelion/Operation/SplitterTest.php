<?php

declare(strict_types=1);

namespace Dandelion\Operation;

use ArrayObject;
use Codeception\Test\Unit;
use Dandelion\Configuration\Configuration;
use Dandelion\Configuration\ConfigurationLoaderInterface;
use Dandelion\Configuration\Repository;
use Dandelion\Configuration\Vcs;
use Dandelion\Console\Command\SplitCommand;
use Dandelion\Operation\Result\MessageFactoryInterface;
use Dandelion\Process\ProcessPoolFactoryInterface;
use Dandelion\Process\ProcessPoolInterface;
use Dandelion\VersionControl\GitInterface;
use Dandelion\VersionControl\Platform\PlatformFactoryInterface;
use Dandelion\VersionControl\Platform\PlatformInterface;
use Dandelion\VersionControl\SplitshLiteInterface;
use Exception;
use Iterator;

use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use function sha1;
use function sprintf;

class SplitterTest extends Unit
{
    /**
     * @var \Dandelion\Operation\Splitter
     */
    protected $splitter;

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
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\VersionControl\GitInterface
     */
    protected $gitMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\VersionControl\SplitshLiteInterface
     */
    protected $splitshLiteMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Lock\LockFactory
     */
    protected $lockFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Lock\LockInterface
     */
    protected $lockMock;


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

        $this->gitMock = $this->getMockBuilder(GitInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->splitshLiteMock = $this->getMockBuilder(SplitshLiteInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->lockFactoryMock = $this->getMockBuilder(LockFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->lockMock = $this->getMockBuilder(LockInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->splitter = new Splitter(
            $this->configurationLoaderMock,
            $this->processPoolFactoryMock,
            $this->resultFactoryMock,
            $this->messageFactoryMock,
            $this->platformFactoryMock,
            $this->gitMock,
            $this->splitshLiteMock,
            $this->lockFactoryMock
        );
    }

    /**
     * @throws \Exception
     *
     * @return void
     */
    public function testExecuteForSingleRepository(): void
    {
        $repositoryPath = '/path/to/package';
        $repositoryName = 'package';
        $repositoryUrl = 'git@github.com:user/package.git';
        $branch = 'master';
        $sha1 = sha1('Lorem Ipsum');

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
            ->method('getRepositoryUrl')
            ->with($repositoryName)
            ->willReturn($repositoryUrl);

        $this->repositoryMock->expects(static::atLeastOnce())
            ->method('getPath')
            ->willReturn($repositoryPath);

        $this->lockFactoryMock->expects(static::atLeastOnce())
            ->method('createLock')
            ->with(Splitter::LOCK_IDENTIFIER)
            ->willReturn($this->lockMock);

        $this->lockMock->expects(static::atLeastOnce())
            ->method('acquire')
            ->with(true)
            ->willReturn(true);

        $this->gitMock->expects(static::atLeastOnce())
            ->method('existsRemote')
            ->with($repositoryName)
            ->willReturn(false);

        $this->gitMock->expects(static::atLeastOnce())
            ->method('addRemote')
            ->with($repositoryName, $repositoryUrl);

        $this->lockMock->expects(static::atLeastOnce())
            ->method('release')
            ->willReturn(true);

        $this->splitshLiteMock->expects(static::atLeastOnce())
            ->method('getSha1')
            ->with($repositoryPath)
            ->willReturn($sha1);

        $this->gitMock->expects(static::atLeastOnce())
            ->method('pushForcefully')
            ->with($repositoryName, sprintf('%s:refs/heads/%s', $sha1, $branch));

        static::assertEquals(
            $this->splitter,
            $this->splitter->executeForSingleRepository($repositoryName, $branch)
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
        $branch = 'master';

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
            ->method('offsetGet')
            ->with($repositoryName);

        $this->lockFactoryMock->expects(static::never())
            ->method('createLock');

        $this->gitMock->expects(static::never())
            ->method('existsRemote');

        $this->gitMock->expects(static::never())
            ->method('addRemote');

        $this->splitshLiteMock->expects(static::never())
            ->method('getSha1');

        $this->gitMock->expects(static::never())
            ->method('pushForcefully');

        try {
            $this->splitter->executeForSingleRepository($repositoryName, $branch);
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
        $branch = 'master';

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
                SplitCommand::NAME,
                $repositoryName,
                $branch
            ])->willReturn($this->processPoolMock);

        $this->processPoolMock->expects(static::atLeastOnce())
            ->method('start')
            ->willReturn($this->processPoolMock);

        static::assertEquals(
            $this->resultMock,
            $this->splitter->executeForAllRepositories([$branch])
        );
    }
}
