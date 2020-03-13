<?php

declare(strict_types=1);

namespace Dandelion\Console\Command;

use Codeception\Test\Unit;
use Dandelion\Operation\SplitterInterface;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SplitCommandTest extends Unit
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
     * @var \Dandelion\Console\Command\SplitCommand
     */
    protected $splitCommand;

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

        $this->splitCommand = new SplitCommand($this->splitterMock);
    }

    /**
     * @return void
     */
    public function testGetName(): void
    {
        $this->assertEquals(SplitCommand::NAME, $this->splitCommand->getName());
    }

    /**
     * @return void
     */
    public function testGetDescription(): void
    {
        $this->assertEquals(SplitCommand::DESCRIPTION, $this->splitCommand->getDescription());
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testRun(): void
    {
        $repositoryName = 'package';
        $branch = 'master';

        $this->inputMock->expects($this->atLeastOnce())
            ->method('getArgument')
            ->withConsecutive(['repositoryName'], ['branch'])
            ->willReturnOnConsecutiveCalls($repositoryName, $branch);

        $this->splitterMock->expects($this->atLeastOnce())
            ->method('split')
            ->with($repositoryName, $branch);

        $this->assertEquals(0, $this->splitCommand->run($this->inputMock, $this->outputMock));
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testRunWithInvalidArgument(): void
    {
        $repositoryName = 'package';
        $branch = 1;

        $this->inputMock->expects($this->atLeastOnce())
            ->method('getArgument')
            ->withConsecutive(['repositoryName'], ['branch'])
            ->willReturnOnConsecutiveCalls($repositoryName, $branch);

        $this->splitterMock->expects($this->never())
            ->method('split');

        try {
            $this->splitCommand->run($this->inputMock, $this->outputMock);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }
}