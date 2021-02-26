<?php

declare(strict_types=1);

namespace Dandelion\Operation;

use ArrayObject;
use Codeception\Test\Unit;
use Dandelion\Configuration\Configuration;
use Dandelion\Configuration\ConfigurationLoaderInterface;
use Dandelion\Configuration\Repository;
use Dandelion\Configuration\Vcs;
use Dandelion\Console\Command\ReleaseCommand;
use Dandelion\Filesystem\FilesystemInterface;
use Dandelion\Operation\Result\MessageFactoryInterface;
use Dandelion\Process\ProcessFactory;
use Dandelion\Process\ProcessPoolFactoryInterface;
use Dandelion\Process\ProcessPoolInterface;
use Dandelion\VersionControl\GitInterface;
use Dandelion\VersionControl\Platform\PlatformFactoryInterface;
use Dandelion\VersionControl\Platform\PlatformInterface;
use Exception;
use Iterator;
use Symfony\Component\Process\Process;

class ReleaserTest extends Unit
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Configuration\ConfigurationLoaderInterface
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
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Filesystem\FilesystemInterface
     */
    protected $filesystemMock;

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
     * @var \Dandelion\Operation\Releaser
     */
    protected $releaser;

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

        $this->filesystemMock = $this->getMockBuilder(FilesystemInterface::class)
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

        $this->releaser = new Releaser(
            $this->configurationLoaderMock,
            $this->filesystemMock,
            $this->processPoolFactoryMock,
            $this->resultFactoryMock,
            $this->messageFactoryMock,
            $this->platformFactoryMock,
            $this->gitMock
        );
    }

    /**
     * @return void
     */
    public function testExecuteForSingleRepository(): void
    {
        $repositoryName = 'package';
        $repositoryUrl = 'git@github.com:user/package.git';
        $branch = 'master';
        $version = '1.0.0';
        $pathToTempDirectory = '/path/to/tempDirectory/';
        $pathToCurrentWorkingDirectory = '/path/to/currentWorkingDirectory/';
        $pathToRepositoryTempDirectory = sprintf('%s%s%s', $pathToTempDirectory, $repositoryName, DIRECTORY_SEPARATOR);

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

        $this->filesystemMock->expects(static::atLeastOnce())
            ->method('createDirectory')
            ->with($pathToRepositoryTempDirectory)
            ->willReturn($this->filesystemMock);

        $this->filesystemMock->expects(static::atLeastOnce())
            ->method('getCurrentWorkingDirectory')
            ->willReturn($pathToCurrentWorkingDirectory);

        $this->configurationMock->expects(static::atLeastOnce())
            ->method('getPathToTempDirectory')
            ->willReturn($pathToTempDirectory);

        $this->filesystemMock->expects(static::atLeastOnce())
            ->method('changeDirectory')
            ->withConsecutive(
                [
                    sprintf('%s%s%s', $pathToTempDirectory, $repositoryName, DIRECTORY_SEPARATOR)
                ],
                [
                    $pathToCurrentWorkingDirectory
                ]
            )
            ->willReturn($this->filesystemMock);

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

        $this->gitMock->expects(static::atLeastOnce())
            ->method('clone')
            ->with($repositoryUrl, '.')
            ->willReturn($this->gitMock);

        $this->gitMock->expects(static::atLeastOnce())
            ->method('checkout')
            ->with($branch)
            ->willReturn($this->gitMock);

        $this->repositoryMock->expects(static::atLeastOnce())
            ->method('getVersion')
            ->willReturn($version);

        $this->gitMock->expects(static::atLeastOnce())
            ->method('describeClosestTag')
            ->with($version)
            ->willReturn(null);

        $this->gitMock->expects(static::atLeastOnce())
            ->method('tag')
            ->with($version)
            ->willReturn($this->gitMock);

        $this->gitMock->expects(static::atLeastOnce())
            ->method('pushWithTags')
            ->with('origin')
            ->willReturn($this->gitMock);

        $this->filesystemMock->expects(static::atLeastOnce())
            ->method('removeDirectory')
            ->with($pathToRepositoryTempDirectory)
            ->willReturn($this->filesystemMock);

        static::assertEquals(
            $this->releaser,
            $this->releaser->executeForSingleRepository($repositoryName, $branch)
        );
    }

    /**
     * @return void
     */
    public function testExecuteForSingleRepositoryWithNonExistingRepository(): void
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

        $this->filesystemMock->expects(static::never())
            ->method('createDirectory');

        $this->filesystemMock->expects(static::never())
            ->method('getCurrentWorkingDirectory');

        $this->configurationMock->expects(static::never())
            ->method('getPathToTempDirectory');

        $this->filesystemMock->expects(static::never())
            ->method('changeDirectory');

        $this->gitMock->expects(static::never())
            ->method('clone');

        $this->gitMock->expects(static::never())
            ->method('checkout');

        $this->gitMock->expects(static::never())
            ->method('describeClosestTag');

        $this->gitMock->expects(static::never())
            ->method('tag');

        $this->gitMock->expects(static::never())
            ->method('pushWithTags');

        try {
            $this->releaser->executeForSingleRepository($repositoryName, $branch);
        } catch (Exception $e) {
            return;
        }

        static::fail();
    }

    /**
     * @return void
     */
    public function testExecuteForSingleRepositoryWithExistingVersion(): void
    {
        $repositoryName = 'package';
        $repositoryUrl = 'git@github.com:user/package.git';
        $branch = 'master';
        $version = '1.0.0';
        $pathToTempDirectory = '/path/to/tempDirectory/';
        $pathToRepositoryTempDirectory = sprintf('%s%s%s', $pathToTempDirectory, $repositoryName, DIRECTORY_SEPARATOR);
        $pathToCurrentWorkingDirectory = '/path/to/currentWorkingDirectory/';

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

        $this->filesystemMock->expects(static::atLeastOnce())
            ->method('createDirectory')
            ->with($pathToRepositoryTempDirectory)
            ->willReturn($this->filesystemMock);

        $this->filesystemMock->expects(static::atLeastOnce())
            ->method('getCurrentWorkingDirectory')
            ->willReturn($pathToCurrentWorkingDirectory);

        $this->configurationMock->expects(static::atLeastOnce())
            ->method('getPathToTempDirectory')
            ->willReturn($pathToTempDirectory);

        $this->filesystemMock->expects(static::atLeastOnce())
            ->method('changeDirectory')
            ->withConsecutive([$pathToRepositoryTempDirectory], [$pathToCurrentWorkingDirectory])
            ->willReturn($this->filesystemMock);

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

        $this->gitMock->expects(static::atLeastOnce())
            ->method('clone')
            ->with($repositoryUrl, '.')
            ->willReturn($this->gitMock);

        $this->gitMock->expects(static::atLeastOnce())
            ->method('checkout')
            ->with($branch)
            ->willReturn($this->gitMock);

        $this->repositoryMock->expects(static::atLeastOnce())
            ->method('getVersion')
            ->willReturn($version);

        $this->gitMock->expects(static::atLeastOnce())
            ->method('describeClosestTag')
            ->with($version)
            ->willReturn($version);

        $this->gitMock->expects(static::never())
            ->method('tag');

        $this->gitMock->expects(static::never())
            ->method('pushWithTags');

        $this->filesystemMock->expects(static::atLeastOnce())
            ->method('removeDirectory')
            ->with($pathToRepositoryTempDirectory)
            ->willReturn($this->filesystemMock);

        static::assertEquals(
            $this->releaser,
            $this->releaser->executeForSingleRepository($repositoryName, $branch)
        );
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
                ReleaseCommand::NAME,
                $repositoryName,
                $branch
            ])->willReturn($this->processPoolMock);

        $this->processPoolMock->expects(static::atLeastOnce())
            ->method('start')
            ->willReturn($this->processPoolMock);

        static::assertEquals(
            $this->resultMock,
            $this->releaser->executeForAllRepositories([$branch])
        );
    }
}
