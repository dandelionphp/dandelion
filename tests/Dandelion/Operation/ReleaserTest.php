<?php

declare(strict_types=1);

namespace Dandelion\Operation;

use ArrayObject;
use Codeception\Test\Unit;
use Dandelion\Configuration\Configuration;
use Dandelion\Configuration\ConfigurationLoaderInterface;
use Dandelion\Configuration\Repository;
use Dandelion\Console\Command\ReleaseCommand;
use Dandelion\Filesystem\FilesystemInterface;
use Dandelion\Operation\Result\MessageFactoryInterface;
use Dandelion\Process\ProcessFactory;
use Dandelion\Process\ProcessPoolFactoryInterface;
use Dandelion\Process\ProcessPoolInterface;
use Dandelion\VersionControl\GitInterface;
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
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\VersionControl\GitInterface
     */
    protected $gitMock;

    /**
     * @var \Dandelion\Operation\AbstractOperation
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

        $this->gitMock = $this->getMockBuilder(GitInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->releaser = new Releaser(
            $this->configurationLoaderMock,
            $this->filesystemMock,
            $this->processPoolFactoryMock,
            $this->resultFactoryMock,
            $this->messageFactoryMock,
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

        $this->configurationLoaderMock->expects($this->atLeastOnce())
            ->method('load')
            ->willReturn($this->configurationMock);

        $this->configurationMock->expects($this->atLeastOnce())
            ->method('getRepositories')
            ->willReturn($this->repositoriesMock);

        $this->repositoriesMock->expects($this->atLeastOnce())
            ->method('offsetExists')
            ->with($repositoryName)
            ->willReturn(true);

        $this->repositoriesMock->expects($this->atLeastOnce())
            ->method('offsetGet')
            ->with($repositoryName)
            ->willReturn($this->repositoryMock);

        $this->filesystemMock->expects($this->atLeastOnce())
            ->method('createDirectory')
            ->with($pathToRepositoryTempDirectory)
            ->willReturn($this->filesystemMock);

        $this->filesystemMock->expects($this->atLeastOnce())
            ->method('getCurrentWorkingDirectory')
            ->willReturn($pathToCurrentWorkingDirectory);

        $this->configurationMock->expects($this->atLeastOnce())
            ->method('getPathToTempDirectory')
            ->willReturn($pathToTempDirectory);

        $this->filesystemMock->expects($this->atLeastOnce())
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

        $this->repositoryMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->willReturn($repositoryUrl);

        $this->gitMock->expects($this->atLeastOnce())
            ->method('clone')
            ->with($repositoryUrl, '.')
            ->willReturn($this->gitMock);

        $this->gitMock->expects($this->atLeastOnce())
            ->method('checkout')
            ->with($branch)
            ->willReturn($this->gitMock);

        $this->repositoryMock->expects($this->atLeastOnce())
            ->method('getVersion')
            ->willReturn($version);

        $this->gitMock->expects($this->atLeastOnce())
            ->method('describeClosestTag')
            ->with($version)
            ->willReturn(null);

        $this->gitMock->expects($this->atLeastOnce())
            ->method('tag')
            ->with($version)
            ->willReturn($this->gitMock);

        $this->gitMock->expects($this->atLeastOnce())
            ->method('pushWithTags')
            ->with('origin')
            ->willReturn($this->gitMock);

        $this->filesystemMock->expects($this->atLeastOnce())
            ->method('removeDirectory')
            ->with($pathToRepositoryTempDirectory)
            ->willReturn($this->filesystemMock);

        $this->assertEquals(
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

        $this->configurationLoaderMock->expects($this->atLeastOnce())
            ->method('load')
            ->willReturn($this->configurationMock);

        $this->configurationMock->expects($this->atLeastOnce())
            ->method('getRepositories')
            ->willReturn($this->repositoriesMock);

        $this->repositoriesMock->expects($this->atLeastOnce())
            ->method('offsetExists')
            ->with($repositoryName)
            ->willReturn(false);

        $this->repositoriesMock->expects($this->never())
            ->method('offsetGet')
            ->with($repositoryName);

        $this->filesystemMock->expects($this->never())
            ->method('createDirectory');

        $this->filesystemMock->expects($this->never())
            ->method('getCurrentWorkingDirectory');

        $this->configurationMock->expects($this->never())
            ->method('getPathToTempDirectory');

        $this->filesystemMock->expects($this->never())
            ->method('changeDirectory');

        $this->repositoryMock->expects($this->never())
            ->method('getUrl');

        $this->gitMock->expects($this->never())
            ->method('clone');

        $this->gitMock->expects($this->never())
            ->method('checkout');

        $this->gitMock->expects($this->never())
            ->method('describeClosestTag');

        $this->gitMock->expects($this->never())
            ->method('tag');

        $this->gitMock->expects($this->never())
            ->method('pushWithTags');

        try {
            $this->releaser->executeForSingleRepository($repositoryName, $branch);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
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

        $this->configurationLoaderMock->expects($this->atLeastOnce())
            ->method('load')
            ->willReturn($this->configurationMock);

        $this->configurationMock->expects($this->atLeastOnce())
            ->method('getRepositories')
            ->willReturn($this->repositoriesMock);

        $this->repositoriesMock->expects($this->atLeastOnce())
            ->method('offsetExists')
            ->with($repositoryName)
            ->willReturn(true);

        $this->repositoriesMock->expects($this->atLeastOnce())
            ->method('offsetGet')
            ->with($repositoryName)
            ->willReturn($this->repositoryMock);

        $this->filesystemMock->expects($this->atLeastOnce())
            ->method('createDirectory')
            ->with($pathToRepositoryTempDirectory)
            ->willReturn($this->filesystemMock);

        $this->filesystemMock->expects($this->atLeastOnce())
            ->method('getCurrentWorkingDirectory')
            ->willReturn($pathToCurrentWorkingDirectory);

        $this->configurationMock->expects($this->atLeastOnce())
            ->method('getPathToTempDirectory')
            ->willReturn($pathToTempDirectory);

        $this->filesystemMock->expects($this->atLeastOnce())
            ->method('changeDirectory')
            ->withConsecutive([$pathToRepositoryTempDirectory], [$pathToCurrentWorkingDirectory])
            ->willReturn($this->filesystemMock);

        $this->repositoryMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->willReturn($repositoryUrl);

        $this->gitMock->expects($this->atLeastOnce())
            ->method('clone')
            ->with($repositoryUrl, '.')
            ->willReturn($this->gitMock);

        $this->gitMock->expects($this->atLeastOnce())
            ->method('checkout')
            ->with($branch)
            ->willReturn($this->gitMock);

        $this->repositoryMock->expects($this->atLeastOnce())
            ->method('getVersion')
            ->willReturn($version);

        $this->gitMock->expects($this->atLeastOnce())
            ->method('describeClosestTag')
            ->with($version)
            ->willReturn($version);

        $this->gitMock->expects($this->never())
            ->method('tag');

        $this->gitMock->expects($this->never())
            ->method('pushWithTags');

        $this->filesystemMock->expects($this->atLeastOnce())
            ->method('removeDirectory')
            ->with($pathToRepositoryTempDirectory)
            ->willReturn($this->filesystemMock);

        $this->assertEquals(
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

        $this->configurationLoaderMock->expects($this->atLeastOnce())
            ->method('load')
            ->willReturn($this->configurationMock);

        $this->processPoolFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->processPoolMock);

        $this->resultFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultMock);

        $this->configurationMock->expects($this->atLeastOnce())
            ->method('getRepositories')
            ->willReturn($this->repositoriesMock);

        $this->repositoriesMock->expects($this->atLeastOnce())
            ->method('getIterator')
            ->willReturn($this->iteratorMock);

        $this->iteratorMock->expects($this->atLeastOnce())
            ->method('rewind');

        $this->iteratorMock->expects($this->atLeastOnce())
            ->method('valid')
            ->willReturnOnConsecutiveCalls(true, false);

        $this->iteratorMock->expects($this->atLeastOnce())
            ->method('current')
            ->willReturn($this->repositoryMock);

        $this->iteratorMock->expects($this->atLeastOnce())
            ->method('key')
            ->willReturn($repositoryName);

        $this->processPoolMock->expects($this->atLeastOnce())
            ->method('addProcess')
            ->with([
                DANDELION_BINARY,
                ReleaseCommand::NAME,
                $repositoryName,
                $branch
            ])->willReturn($this->processPoolMock);

        $this->processPoolMock->expects($this->atLeastOnce())
            ->method('start')
            ->willReturn($this->processPoolMock);

        $this->assertEquals(
            $this->resultMock,
            $this->releaser->executeForAllRepositories($branch)
        );
    }
}
