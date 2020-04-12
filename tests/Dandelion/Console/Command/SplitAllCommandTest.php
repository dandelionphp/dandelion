<?php

declare(strict_types=1);

namespace Dandelion\Console\Command;

use Codeception\Test\Unit;
use Dandelion\Operation\AbstractOperation;
use Dandelion\Operation\Result\MessageInterface;
use Dandelion\Operation\ResultInterface;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

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
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Operation\ResultInterface
     */
    protected $resultMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject[]|\Dandelion\Operation\Result\MessageInterface[]
     */
    protected $messageMocks;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Operation\AbstractOperation
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

        $this->resultMock = $this->getMockBuilder(ResultInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageMocks = [
            $this->getMockBuilder(MessageInterface::class)
                ->disableOriginalConstructor()
                ->getMock()
        ];

        $this->splitterMock = $this->getMockBuilder(AbstractOperation::class)
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
        $messageText = 'Lorem ipsum';

        $this->inputMock->expects($this->atLeastOnce())
            ->method('getArgument')
            ->with('branch')
            ->willReturn($branch);

        $this->splitterMock->expects($this->atLeastOnce())
            ->method('executeForAllRepositories')
            ->with($branch)
            ->willReturn($this->resultMock);

        $this->resultMock->expects($this->atLeastOnce())
            ->method('getMessages')
            ->willReturn($this->messageMocks);

        $this->messageMocks[0]->expects($this->atLeastOnce())
            ->method('getType')
            ->willReturn(MessageInterface::TYPE_INFO);

        $this->messageMocks[0]->expects($this->atLeastOnce())
            ->method('getText')
            ->willReturn($messageText);

        $this->outputMock->expects($this->atLeastOnce())
            ->method('writeln')
            ->withConsecutive(
                ['Splitting monorepo packages:'],
                ['---------------------------------'],
                [sprintf('<fg=green>✔</> %s', $messageText)],
                ['---------------------------------'],
                ['Finished']
            );

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
            ->method('executeForAllRepositories');

        try {
            $this->splitAllCommand->run($this->inputMock, $this->outputMock);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }
}
