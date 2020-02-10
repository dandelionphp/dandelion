<?php

namespace Dandelion\Operation;

use ArrayObject;
use Codeception\Test\Unit;
use Dandelion\Configuration\Configuration;
use Dandelion\Configuration\ConfigurationLoaderInterface;
use Dandelion\Configuration\Repository;
use Dandelion\Console\Command\ReleaseCommand;
use Dandelion\Filesystem\FilesystemInterface;
use Dandelion\Process\ProcessFactory;
use Dandelion\VersionControl\GitInterface;
use Exception;
use Iterator;
use Symfony\Component\Process\Process;

class ReleaserTest extends Unit
{
    /**
     * @var string
     */
    protected $pathToBinDirectory;

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
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Process\ProcessFactory
     */
    protected $processFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Process\Process
     */
    protected $processMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\VersionControl\GitInterface
     */
    protected $gitMock;
    /**
     * @var \Dandelion\Operation\ReleaserInterface
     */
    protected $releaser;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->pathToBinDirectory = '/path/to/bin/directory';

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

        $this->processFactoryMock = $this->getMockBuilder(ProcessFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->processMock = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->gitMock = $this->getMockBuilder(GitInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->releaser = new Releaser(
            $this->configurationLoaderMock,
            $this->filesystemMock,
            $this->processFactoryMock,
            $this->gitMock,
            $this->pathToBinDirectory
        );
    }

    /**
     * @return void
     */
    public function testRelease(): void
    {
        $pathToTempDirectory = '/path/to/tempDirectory/';
        $pathToCurrentWorkingDirectory = '/path/to/currentWorkingDirectory/';
        $repositoryName = 'package';
        $repositoryUrl = 'git@github.com:user/package.git';
        $branch = 'master';
        $version = '1.0.0';

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
                ], [
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

        $this->gitMock->expects($this->atLeastOnce())
            ->method('tag')
            ->with($version)
            ->willReturn($this->gitMock);

        $this->gitMock->expects($this->atLeastOnce())
            ->method('pushWithTags')
            ->with('origin')
            ->willReturn($this->gitMock);

        $this->assertEquals(
            $this->releaser,
            $this->releaser->release($repositoryName, $branch, $version)
        );
    }

    /**
     * @return void
     */
    public function testReleaseWithNonExistingRepository(): void
    {
        $repositoryName = 'package';
        $branch = 'master';
        $version = '1.0.0';

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
            ->method('tag');

        $this->gitMock->expects($this->never())
            ->method('pushWithTags');

        try {
            $this->releaser->release($repositoryName, $branch, $version);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @return void
     */
    public function testReleaseAll(): void
    {
        $repositoryName = 'package';
        $branch = 'master';
        $version = '1.0.0';

        $this->configurationLoaderMock->expects($this->atLeastOnce())
            ->method('load')
            ->willReturn($this->configurationMock);

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

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with([
                sprintf('%sdandelion', $this->pathToBinDirectory),
                ReleaseCommand::NAME,
                $repositoryName,
                $branch,
                $version
            ])->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('start');

        $this->assertEquals(
            $this->releaser,
            $this->releaser->releaseAll($branch, $version)
        );
    }
}