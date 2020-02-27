<?php

declare(strict_types=1);

namespace Dandelion\VersionControl;

use Codeception\Test\Unit;
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
     * @var string
     */
    protected $pathToBinDirectory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Environment\OperatingSystemInterface
     */
    protected $operatingSystemMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Process\ProcessFactory
     */
    protected $processFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Process\Process
     */
    protected $processMock;

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

        $this->pathToBinDirectory = '/path/To/Bin/Directory';

        $this->operatingSystemMock = $this->getMockBuilder(OperatingSystemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->processFactoryMock = $this->getMockBuilder(ProcessFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->processMock = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->splitshLite = new SplitshLite(
            $this->operatingSystemMock,
            $this->processFactoryMock,
            $this->pathToBinDirectory
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
        $pathToSplitshLite = sprintf(
            '%s%s-%s',
            $this->pathToBinDirectory,
            'splitsh-lite',
            strtolower(OperatingSystem::FAMILY_DARWIN)
        );

        $this->operatingSystemMock->expects($this->atLeastOnce())
            ->method('getMachineType')
            ->willReturn(OperatingSystem::MACHINE_TYPE_X86_64);

        $this->operatingSystemMock->expects($this->atLeastOnce())
            ->method('getFamily')
            ->willReturn(OperatingSystem::FAMILY_DARWIN);

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with([
                $pathToSplitshLite,
                sprintf('--prefix=%s', $pathToPackage)
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
    public function testGetSha1WithUnsupportedMachineType(): void
    {
        $pathToPackage = '/path/to/package';

        $this->operatingSystemMock->expects($this->atLeastOnce())
            ->method('getMachineType')
            ->willReturn('i386');

        $this->operatingSystemMock->expects($this->never())
            ->method('getFamily')
            ->willReturn(OperatingSystem::FAMILY_DARWIN);

        $this->processFactoryMock->expects($this->never())
            ->method('create');

        $this->processMock->expects($this->never())
            ->method('run');

        $this->processMock->expects($this->never())
            ->method('isSuccessful');

        $this->processMock->expects($this->never())
            ->method('getOutput');

        try {
            $this->splitshLite->getSha1($pathToPackage);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testGetSha1WithUnsupportedOsFamily(): void
    {

        $pathToPackage = '/path/to/package';

        $this->operatingSystemMock->expects($this->atLeastOnce())
            ->method('getMachineType')
            ->willReturn(OperatingSystem::MACHINE_TYPE_X86_64);

        $this->operatingSystemMock->expects($this->atLeastOnce())
            ->method('getFamily')
            ->willReturn('Unknown');

        $this->processFactoryMock->expects($this->never())
            ->method('create');

        $this->processMock->expects($this->never())
            ->method('run');

        $this->processMock->expects($this->never())
            ->method('isSuccessful');

        $this->processMock->expects($this->never())
            ->method('getOutput');

        try {
            $this->splitshLite->getSha1($pathToPackage);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testGetSha1WithError(): void
    {
        $pathToPackage = '/path/to/package';
        $pathToSplitshLite = sprintf(
            '%s%s-%s',
            $this->pathToBinDirectory,
            'splitsh-lite',
            strtolower(OperatingSystem::FAMILY_DARWIN)
        );

        $this->operatingSystemMock->expects($this->atLeastOnce())
            ->method('getMachineType')
            ->willReturn(OperatingSystem::MACHINE_TYPE_X86_64);

        $this->operatingSystemMock->expects($this->atLeastOnce())
            ->method('getFamily')
            ->willReturn(OperatingSystem::FAMILY_DARWIN);

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with([
                $pathToSplitshLite,
                sprintf('--prefix=%s', $pathToPackage)
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