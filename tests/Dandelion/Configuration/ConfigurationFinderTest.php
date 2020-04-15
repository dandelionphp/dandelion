<?php

declare(strict_types=1);

namespace Dandelion\Configuration;

use Codeception\Test\Unit;
use Dandelion\Filesystem\FilesystemInterface;
use Exception;
use Iterator;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class ConfigurationFinderTest extends Unit
{
    /**
     * @var string
     */
    protected $currentWorkingDirectory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Finder\Finder
     */
    protected $symfonyFinderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Iterator
     */
    protected $iteratorMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\SplFileInfo
     */
    protected $splFileInfoMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Filesystem\FilesystemInterface
     */
    protected $filesystemMock;

    /**
     * @var \Dandelion\Configuration\ConfigurationFinderInterface
     */
    protected $configurationFinder;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->currentWorkingDirectory = '/c/w/d';

        $this->symfonyFinderMock = $this->getMockBuilder(Finder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->iteratorMock = $this->getMockBuilder(Iterator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->splFileInfoMock = $this->getMockBuilder(SplFileInfo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystemMock = $this->getMockBuilder(FilesystemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurationFinder = new ConfigurationFinder(
            $this->symfonyFinderMock,
            $this->filesystemMock
        );
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testFind(): void
    {
        $this->symfonyFinderMock->expects($this->atLeastOnce())
            ->method('files')
            ->willReturn($this->symfonyFinderMock);

        $this->filesystemMock->expects($this->atLeastOnce())
            ->method('getCurrentWorkingDirectory')
            ->willReturn($this->currentWorkingDirectory);

        $this->symfonyFinderMock->expects($this->atLeastOnce())
            ->method('in')
            ->with($this->currentWorkingDirectory)
            ->willReturn($this->symfonyFinderMock);

        $this->symfonyFinderMock->expects($this->atLeastOnce())
            ->method('name')
            ->with('dandelion.json')
            ->willReturn($this->symfonyFinderMock);

        $this->symfonyFinderMock->expects($this->atLeastOnce())
            ->method('depth')
            ->with('== 0')
            ->willReturn($this->symfonyFinderMock);

        $this->symfonyFinderMock->expects($this->atLeastOnce())
            ->method('count')
            ->willReturn(1);

        $this->symfonyFinderMock->expects($this->atLeastOnce())
            ->method('getIterator')
            ->willReturn($this->iteratorMock);

        $this->iteratorMock->expects($this->atLeastOnce())
            ->method('rewind');

        $this->iteratorMock->expects($this->atLeastOnce())
            ->method('valid')
            ->willReturn(true);

        $this->iteratorMock->expects($this->atLeastOnce())
            ->method('current')
            ->willReturn($this->splFileInfoMock);

        $this->assertEquals($this->splFileInfoMock, $this->configurationFinder->find());
    }

    /**
     * @return void
     */
    public function testFindWithNonExistingConfigurationFile(): void
    {
        $this->symfonyFinderMock->expects($this->atLeastOnce())
            ->method('files')
            ->willReturn($this->symfonyFinderMock);

        $this->filesystemMock->expects($this->atLeastOnce())
            ->method('getCurrentWorkingDirectory')
            ->willReturn($this->currentWorkingDirectory);

        $this->symfonyFinderMock->expects($this->atLeastOnce())
            ->method('in')
            ->with($this->currentWorkingDirectory)
            ->willReturn($this->symfonyFinderMock);

        $this->symfonyFinderMock->expects($this->atLeastOnce())
            ->method('name')
            ->with('dandelion.json')
            ->willReturn($this->symfonyFinderMock);

        $this->symfonyFinderMock->expects($this->atLeastOnce())
            ->method('depth')
            ->with('== 0')
            ->willReturn($this->symfonyFinderMock);

        $this->symfonyFinderMock->expects($this->atLeastOnce())
            ->method('count')
            ->willReturn(0);

        $this->symfonyFinderMock->expects($this->never())
            ->method('getIterator');

        $this->iteratorMock->expects($this->never())
            ->method('rewind');

        $this->iteratorMock->expects($this->never())
            ->method('valid');

        $this->iteratorMock->expects($this->never())
            ->method('current');

        try {
            $this->configurationFinder->find();
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @return void
     */
    public function testFindWithInvalidIteratorPosition(): void
    {
        $this->symfonyFinderMock->expects($this->atLeastOnce())
            ->method('files')
            ->willReturn($this->symfonyFinderMock);

        $this->filesystemMock->expects($this->atLeastOnce())
            ->method('getCurrentWorkingDirectory')
            ->willReturn($this->currentWorkingDirectory);

        $this->symfonyFinderMock->expects($this->atLeastOnce())
            ->method('in')
            ->with($this->currentWorkingDirectory)
            ->willReturn($this->symfonyFinderMock);

        $this->symfonyFinderMock->expects($this->atLeastOnce())
            ->method('name')
            ->with('dandelion.json')
            ->willReturn($this->symfonyFinderMock);

        $this->symfonyFinderMock->expects($this->atLeastOnce())
            ->method('depth')
            ->with('== 0')
            ->willReturn($this->symfonyFinderMock);

        $this->symfonyFinderMock->expects($this->atLeastOnce())
            ->method('count')
            ->willReturn(1);

        $this->symfonyFinderMock->expects($this->atLeastOnce())
            ->method('getIterator')
            ->willReturn($this->iteratorMock);

        $this->iteratorMock->expects($this->atLeastOnce())
            ->method('rewind');

        $this->iteratorMock->expects($this->atLeastOnce())
            ->method('valid')
            ->willReturn(false);

        $this->iteratorMock->expects($this->never())
            ->method('current');

        try {
            $this->configurationFinder->find();
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }
}
