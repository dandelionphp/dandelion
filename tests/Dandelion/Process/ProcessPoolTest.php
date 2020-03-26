<?php

namespace Dandelion\Process;

use Closure;
use Codeception\Test\Unit;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

class ProcessPoolTest extends Unit
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Process\ProcessFactory
     */
    protected $processFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Log\LoggerInterface
     */
    protected $loggerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Process\Process
     */
    protected $processMock;

    /**
     * @var \Dandelion\Process\ProcessPool
     */
    protected $processPool;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->processFactoryMock = $this->getMockBuilder(ProcessFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->processMock = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->processPool = new ProcessPool(
            $this->processFactoryMock,
            $this->loggerMock
        );
    }

    /**
     * @return void
     */
    public function testAddProcessByCommand(): void
    {
        $command = ['ls', '-la'];

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with($command)
            ->willReturn($this->processMock);

        $this->assertEquals($this->processPool, $this->processPool->addProcessByCommand($command));
    }

    /**
     * @return void
     */
    public function testStart(): void
    {
        $this->assertEquals($this->processPool, $this->processPool->start());
    }

    /**
     * @return void
     */
    public function testAddProcessByCommandAndStart(): void
    {
        $command = ['ls', '-la'];

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with($command)
            ->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('start')
            ->with($this->isInstanceOf(Closure::class))
            ->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('isRunning')
            ->willReturnOnConsecutiveCalls(true, false);

        $this->assertEquals($this->processPool, $this->processPool->addProcessByCommand($command));
        $this->assertEquals($this->processPool, $this->processPool->start());
    }
}
