<?php

namespace Dandelion\Console\Command;

use Codeception\Test\Unit;
use Dandelion\Operation\ReleaserInterface;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReleaseAllCommandTest extends Unit
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Console\Input\InputInterface
     */
    protected $inputMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Console\Output\OutputInterface
     */
    protected $outputMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Operation\ReleaserInterface
     */
    protected $releaserMock;

    /**
     * @var \Dandelion\Console\Command\ReleaseAllCommand
     */
    protected $releaseAllCommand;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->inputMock = $this->getMockBuilder(InputInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->outputMock = $this->getMockBuilder(OutputInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->releaserMock = $this->getMockBuilder(ReleaserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->releaseAllCommand = new ReleaseAllCommand($this->releaserMock);
    }

    /**
     * @return void
     */
    public function testGetName(): void
    {
        $this->assertEquals(ReleaseAllCommand::NAME, $this->releaseAllCommand->getName());
    }

    /**
     * @return void
     */
    public function testGetDescription(): void
    {
        $this->assertEquals(ReleaseAllCommand::DESCRIPTION, $this->releaseAllCommand->getDescription());
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testRun(): void
    {
        $branch = 'master';
        $version = '1.0.0';

        $this->inputMock->expects($this->atLeastOnce())
            ->method('getArgument')
            ->withConsecutive(['branch'], ['version'])
            ->willReturnOnConsecutiveCalls(
                $branch, $version
            );

        $this->releaserMock->expects($this->atLeastOnce())
            ->method('releaseAll')
            ->with($branch, $version);

        $this->assertEquals(0, $this->releaseAllCommand->run($this->inputMock, $this->outputMock));
    }

    /**
     * @return void
     */
    public function testRunWithInvalidArgument(): void
    {
        $branch = 1;
        $version = '1.0.0';

        $this->inputMock->expects($this->atLeastOnce())
            ->method('getArgument')
            ->withConsecutive(['branch'], ['version'])
            ->willReturnOnConsecutiveCalls(
                $branch, $version
            );

        $this->releaserMock->expects($this->never())
            ->method('releaseAll');

        try {
            $this->releaseAllCommand->run($this->inputMock, $this->outputMock);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }
}
