<?php

declare(strict_types=1);

namespace Dandelion\VersionControl;

use Codeception\Test\Unit;
use Dandelion\Configuration\Configuration;
use Dandelion\Configuration\ConfigurationLoaderInterface;
use Dandelion\Environment\OperatingSystem;
use Dandelion\Environment\OperatingSystemInterface;
use Dandelion\Process\ProcessFactory;
use Exception;
use Symfony\Component\Process\Process;

use function sha1;
use function sprintf;
use function strtolower;

class SplitshLiteTest extends Unit
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Process\ProcessFactory
     */
    protected $processFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Process\Process
     */
    protected $processMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Configuration\ConfigurationLoaderInterface
     */
    protected $configurationLoaderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Configuration\Configuration
     */
    protected $configurationMock;

    /**
     * @var \Dandelion\VersionControl\SplitshLiteInterface
     */
    protected $splitshLite;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->processFactoryMock = $this->getMockBuilder(ProcessFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->processMock = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurationLoaderMock = $this->getMockBuilder(ConfigurationLoaderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurationMock = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->splitshLite = new SplitshLite(
            $this->processFactoryMock,
            $this->configurationLoaderMock
        );
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testGetSha1(): void
    {
        $expectedSha1 = sha1('Lorem ipsum');
        $pathToPackage = '/path/to/package';
        $pathToBinary = '/usr/bin/splitsh-lite';

        $this->configurationLoaderMock->expects($this->atLeastOnce())
            ->method('load')
            ->willReturn($this->configurationMock);

        $this->configurationMock->expects($this->atLeastOnce())
            ->method('getPathToSplitshLite')
            ->willReturn($pathToBinary);

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with([
                $pathToBinary,
                sprintf('--prefix=%s', $pathToPackage),
                '--quiet'
            ])->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('run');

        $this->processMock->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(true);

        $this->processMock->expects($this->atLeastOnce())
            ->method('getOutput')
            ->willReturn($expectedSha1);

        $this->assertEquals(
            $expectedSha1,
            $this->splitshLite->getSha1($pathToPackage)
        );
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testGetSha1WithDefaultPathToBinary(): void
    {
        $expectedSha1 = sha1('Lorem ipsum');
        $pathToPackage = '/path/to/package';

        $this->configurationLoaderMock->expects($this->atLeastOnce())
            ->method('load')
            ->willReturn($this->configurationMock);

        $this->configurationMock->expects($this->atLeastOnce())
            ->method('getPathToSplitshLite')
            ->willReturn(null);

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with([
                SplitshLite::DEFAULT_PATH_TO_BINARY,
                sprintf('--prefix=%s', $pathToPackage),
                '--quiet'
            ])->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('run');

        $this->processMock->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(true);

        $this->processMock->expects($this->atLeastOnce())
            ->method('getOutput')
            ->willReturn($expectedSha1);

        $this->assertEquals(
            $expectedSha1,
            $this->splitshLite->getSha1($pathToPackage)
        );
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testGetSha1WithError(): void
    {
        $expectedSha1 = sha1('Lorem ipsum');
        $pathToPackage = '/path/to/package';

        $this->configurationLoaderMock->expects($this->atLeastOnce())
            ->method('load')
            ->willReturn($this->configurationMock);

        $this->configurationMock->expects($this->atLeastOnce())
            ->method('getPathToSplitshLite')
            ->willReturn(null);

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with([
                SplitshLite::DEFAULT_PATH_TO_BINARY,
                sprintf('--prefix=%s', $pathToPackage),
                '--quiet'
            ])->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('run');

        $this->processMock->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(false);

        $this->processMock->expects($this->never())
            ->method('getOutput');

        try {
            $this->splitshLite->getSha1($pathToPackage);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }
}
