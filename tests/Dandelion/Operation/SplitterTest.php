<?php

declare(strict_types=1);

namespace Dandelion\Operation;

use ArrayObject;
use Codeception\Test\Unit;
use Dandelion\Configuration\Configuration;
use Dandelion\Configuration\ConfigurationLoaderInterface;
use Dandelion\Configuration\Repository;
use Dandelion\Console\Command\SplitCommand;
use Dandelion\Operation\Result\MessageFactoryInterface;
use Dandelion\Process\ProcessPoolFactoryInterface;
use Dandelion\Process\ProcessPoolInterface;
use Dandelion\VersionControl\GitInterface;
use Dandelion\VersionControl\SplitshLiteInterface;
use Exception;
use Iterator;

use function sha1;
use function sprintf;

class SplitterTest extends Unit
{
    /**
     * @var \Dandelion\Operation\AbstractOperation
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

        $this->splitshLiteMock = $this->getMockBuilder(SplitshLiteInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->splitter = new Splitter(
            $this->configurationLoaderMock,
            $this->processPoolFactoryMock,
            $this->resultFactoryMock,
            $this->messageFactoryMock,
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
    public function testExecuteForSingleRepository(): void
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
            ->method('existsRemote')
            ->with($repositoryName)
            ->willReturn(false);

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
            ->method('existsRemote');

        $this->gitMock->expects($this->never())
            ->method('addRemote');

        $this->splitshLiteMock->expects($this->never())
            ->method('getSha1');

        $this->gitMock->expects($this->never())
            ->method('pushForcefully');

        try {
            $this->splitter->executeForSingleRepository($repositoryName, $branch);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
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
                sprintf('%sdandelion', $this->pathToBinDirectory),
                SplitCommand::NAME,
                $repositoryName,
                $branch
            ])->willReturn($this->processPoolMock);

        $this->processPoolMock->expects($this->atLeastOnce())
            ->method('start')
            ->willReturn($this->processPoolMock);

        $this->assertEquals(
            $this->resultMock,
            $this->splitter->executeForAllRepositories()
        );
    }
}
