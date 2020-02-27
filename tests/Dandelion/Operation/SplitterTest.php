<?php

namespace Dandelion\Operation;

use ArrayObject;
use Codeception\Test\Unit;
use Dandelion\Configuration\Configuration;
use Dandelion\Configuration\ConfigurationLoaderInterface;
use Dandelion\Configuration\Repository;
use Dandelion\Console\Command\SplitCommand;
use Dandelion\Process\ProcessFactory;
use Dandelion\VersionControl\GitInterface;
use Dandelion\VersionControl\SplitshLiteInterface;
use Exception;
use Iterator;
use Symfony\Component\Process\Process;
use function sha1;
use function sprintf;

class SplitterTest extends Unit
{
    /**
     * @var \Dandelion\Operation\SplitterInterface
     */
    protected $splitter;

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
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\VersionControl\SplitshLiteInterface
     */
    protected $splitshLiteMock;

    /**
     * @var string
     */
    protected $pathToBinDirectory;

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

        $this->processFactoryMock = $this->getMockBuilder(ProcessFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->processMock = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->gitMock = $this->getMockBuilder(GitInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->splitshLiteMock = $this->getMockBuilder(SplitshLiteInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->splitter = new Splitter(
            $this->configurationLoaderMock,
            $this->processFactoryMock,
            $this->gitMock,
            $this->splitshLiteMock,
            $this->pathToBinDirectory
        );
    }

    /**
     * @throws \Exception
     *
     * @return void
     */
    public function testSplit(): void
    {
        $repositoryPath = '/path/to/package';
        $repositoryName = 'package';
        $repositoryUrl = 'git@github.com:user/package.git';
        $branch = 'master';
        $sha1 = sha1('Lorem Ipsum');

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

        $this->repositoryMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->willReturn($repositoryUrl);

        $this->repositoryMock->expects($this->atLeastOnce())
            ->method('getPath')
            ->willReturn($repositoryPath);

        $this->gitMock->expects($this->atLeastOnce())
            ->method('addRemote')
            ->with($repositoryName, $repositoryUrl);

        $this->splitshLiteMock->expects($this->atLeastOnce())
            ->method('getSha1')
            ->with($repositoryPath)
            ->willReturn($sha1);

        $this->gitMock->expects($this->atLeastOnce())
            ->method('pushForcefully')
            ->with($repositoryName, sprintf('%s:refs/heads/%s', $sha1, $branch));

        $this->assertEquals(
            $this->splitter,
            $this->splitter->split($repositoryName, $branch)
        );
    }

    /**
     * @throws \Exception
     *
     * @return void
     */
    public function testSplitWithNonExistingRepository(): void
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

        $this->repositoryMock->expects($this->never())
            ->method('getUrl');

        $this->repositoryMock->expects($this->never())
            ->method('getPath');

        $this->gitMock->expects($this->never())
            ->method('addRemote');

        $this->splitshLiteMock->expects($this->never())
            ->method('getSha1');

        $this->gitMock->expects($this->never())
            ->method('pushForcefully');

        try {
            $this->splitter->split($repositoryName, $branch);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @return void
     */
    public function testSplitAll(): void
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
                SplitCommand::NAME,
                $repositoryName,
                $branch
            ])->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('start');

        $this->assertEquals(
            $this->splitter,
            $this->splitter->splitAll()
        );
    }
}