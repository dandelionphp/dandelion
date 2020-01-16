<?php

namespace DR\Monorepo\Operation;

use Codeception\Test\Unit;
use DR\Monorepo\Configuration\Configuration;
use DR\Monorepo\Configuration\ConfigurationInterface;
use DR\Monorepo\Configuration\ConfigurationLoaderInterface;
use DR\Monorepo\Configuration\RepositoryInterface;
use DR\Monorepo\Console\Command\SplitCommand;
use DR\Monorepo\Process\ProcessFactory;
use DR\Monorepo\VersionControl\GitInterface;
use DR\Monorepo\VersionControl\SplitshLiteInterface;
use Symfony\Component\Process\Process;
use function sha1;
use function sprintf;

class SplitterTest extends Unit
{
    /**
     * @var \DR\Monorepo\Operation\SplitterInterface
     */
    protected $splitter;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\DR\Monorepo\Configuration\ConfigurationLoaderInterface
     */
    protected $configurationLoaderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\DR\Monorepo\Configuration\ConfigurationInterface
     */
    protected $configurationMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\ArrayObject
     */
    protected $repositoriesMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\DR\Monorepo\Configuration\RepositoryInterface
     */
    protected $repositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\DR\Monorepo\Process\ProcessFactory
     */
    protected $processFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Process\Process
     */
    protected $processMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\DR\Monorepo\VersionControl\GitInterface
     */
    protected $gitMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\DR\Monorepo\VersionControl\SplitshLiteInterface
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

        $this->pathToBinDirectory = '/path/To/Bin/Directory';

        $this->configurationLoaderMock = $this->getMockBuilder(ConfigurationLoaderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurationMock = $this->getMockBuilder(ConfigurationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->repositoriesMock = $this->getMockBuilder(RepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->repositoryMock = $this->getMockBuilder(RepositoryInterface::class)
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
            ->willReturn($this->configurationMock);

        $this->repositoryMocks->offsetGet($repositoryName)
            ->expects($this->atLeastOnce())
            ->method('getUrl')
            ->willReturn($repositoryUrl);

        $this->repositoryMocks->offsetGet($repositoryName)
            ->expects($this->atLeastOnce())
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
            ->method('push')
            ->with($repository, sprintf('%s:refs/heads/%s', $sha1, $branch), false, true);

        $this->assertEquals(
            $this->splitter,
            $this->splitter->split($pathToPackage, $repository, $branch)
        );
    }

    /**
     * @return void
     */
    public function testSplitAll(): void
    {
        $branch = 'master';
        $pathToRepository = '/path/to/package';
        $repository = 'git@github.com:user/package.git';
        $repositories = [$pathToRepository => $repository];

        $configurationMock = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurationLoaderMock->expects($this->atLeastOnce())
            ->method('load')
            ->willReturn($configurationMock);

        $configurationMock->expects($this->atLeastOnce())
            ->method('getRepositories')
            ->willReturn($repositories);

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with([
                sprintf('%smonorepo', $this->pathToBinDirectory),
                SplitCommand::NAME,
                $pathToRepository,
                $repository,
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