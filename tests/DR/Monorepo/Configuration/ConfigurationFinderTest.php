<?php

namespace DR\Monorepo\Configuration;

use Codeception\Test\Unit;
use Exception;
use Iterator;
use Symfony\Component\Finder\Finder;
use SplFileInfo;
use function file_put_contents;
use function getcwd;
use function rtrim;

class ConfigurationFinderTest extends Unit
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Finder\Finder
     */
    protected $symfonyFinderMock;

    /**
     * @var \DR\Monorepo\Configuration\ConfigurationFinder
     */
    protected $configurationFinder;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Iterator
     */
    protected $iteratorMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\SplFileInfo
     */
    protected $splFileInfoMock;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->symfonyFinderMock = $this->getMockBuilder(Finder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->iteratorMock = $this->getMockBuilder(Iterator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->splFileInfoMock = $this->getMockBuilder(SplFileInfo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurationFinder = new ConfigurationFinder($this->symfonyFinderMock);
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

        $this->symfonyFinderMock->expects($this->atLeastOnce())
            ->method('in')
            ->with(getcwd())
            ->willReturn($this->symfonyFinderMock);

        $this->symfonyFinderMock->expects($this->atLeastOnce())
            ->method('name')
            ->with('monorepo.json')
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

        $this->symfonyFinderMock->expects($this->atLeastOnce())
            ->method('in')
            ->with(getcwd())
            ->willReturn($this->symfonyFinderMock);

        $this->symfonyFinderMock->expects($this->atLeastOnce())
            ->method('name')
            ->with('monorepo.json')
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
            $this->fail();
        } catch (Exception $e) {}
    }

    /**
     * @return void
     */
    public function testFindWithInvalidIteratorPosition(): void
    {
        $this->symfonyFinderMock->expects($this->atLeastOnce())
            ->method('files')
            ->willReturn($this->symfonyFinderMock);

        $this->symfonyFinderMock->expects($this->atLeastOnce())
            ->method('in')
            ->with(getcwd())
            ->willReturn($this->symfonyFinderMock);

        $this->symfonyFinderMock->expects($this->atLeastOnce())
            ->method('name')
            ->with('monorepo.json')
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
            $this->fail();
        } catch (Exception $e) {}
    }
}