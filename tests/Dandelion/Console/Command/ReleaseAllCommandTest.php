<?php

declare(strict_types=1);

namespace Dandelion\Console\Command;

use Codeception\Test\Unit;
use Dandelion\Operation\AbstractOperation;
use Dandelion\Operation\ReleaserInterface;
use Dandelion\Operation\Result\MessageInterface;
use Dandelion\Operation\ResultInterface;
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
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Operation\ResultInterface
     */
    protected $resultMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject[]|\Dandelion\Operation\Result\MessageInterface[]
     */
    protected $messageMocks;

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

        $this->resultMock = $this->getMockBuilder(ResultInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageMocks = [
            $this->getMockBuilder(MessageInterface::class)
                ->disableOriginalConstructor()
                ->getMock()
        ];

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
        $messageText = 'Lorem ipsum';

        $this->inputMock->expects($this->atLeastOnce())
            ->method('getArgument')
            ->with('branch')
            ->willReturn($branch);

        $this->releaserMock->expects($this->atLeastOnce())
            ->method('executeForAllRepositories')
            ->with(
                $this->callback(
                    static function(array $arguments) use ($branch) {
                        return count($arguments) === 1 && $arguments[0] === $branch;
                    }
                )
            )->willReturn($this->resultMock);

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
                ['Releasing monorepo packages:'],
                ['---------------------------------'],
                [sprintf('<fg=green>✔</> %s', $messageText)],
                ['---------------------------------'],
                ['Finished']
            );

        $this->assertEquals(0, $this->releaseAllCommand->run($this->inputMock, $this->outputMock));
    }

    /**
     * @return void
     */
    public function testRunWithInvalidArgument(): void
    {
        $branch = 1;

        $this->inputMock->expects($this->atLeastOnce())
            ->method('getArgument')
            ->with('branch')
            ->willReturn($branch);

        $this->releaserMock->expects($this->never())
            ->method('executeForAllRepositories');

        try {
            $this->releaseAllCommand->run($this->inputMock, $this->outputMock);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }
}
