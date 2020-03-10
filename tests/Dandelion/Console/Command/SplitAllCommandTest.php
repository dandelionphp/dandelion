<?php

declare(strict_types=1);

namespace Dandelion\Console\Command;

use Codeception\Test\Unit;
use Dandelion\Operation\SplitterInterface;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SplitAllCommandTest extends Unit
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
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Operation\SplitterInterface
     */
    protected $splitterMock;

    /**
     * @var \Dandelion\Console\Command\SplitAllCommand
     */
    protected $splitAllCommand;

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

        $this->splitterMock = $this->getMockBuilder(SplitterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->splitAllCommand = new SplitAllCommand($this->splitterMock);
    }

    /**
     * @return void
     */
    public function testGetName(): void
    {
        $this->assertEquals(SplitAllCommand::NAME, $this->splitAllCommand->getName());
    }

    /**
     * @return void
     */
    public function testGetDescription(): void
    {
        $this->assertEquals(SplitAllCommand::DESCRIPTION, $this->splitAllCommand->getDescription());
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testRun(): void
    {
        $branch = 'master';

        $this->inputMock->expects($this->atLeastOnce())
            ->method('getArgument')
            ->with('branch')
            ->willReturn($branch);

        $this->splitterMock->expects($this->atLeastOnce())
            ->method('splitAll')
            ->with($branch);

        $this->assertEquals(0, $this->splitAllCommand->run($this->inputMock, $this->outputMock));
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testRunWithInvalidArgument(): void
    {
        $branch = 1;

        $this->inputMock->expects($this->atLeastOnce())
            ->method('getArgument')
            ->with('branch')
            ->willReturn($branch);

        $this->splitterMock->expects($this->never())
            ->method('splitAll');

        try {
            $this->splitAllCommand->run($this->inputMock, $this->outputMock);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }
}
