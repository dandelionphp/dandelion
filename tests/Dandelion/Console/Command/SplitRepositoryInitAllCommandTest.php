<?php

declare(strict_types=1);

namespace Dandelion\Console\Command;

use Codeception\Test\Unit;
use Dandelion\Operation\Result\MessageInterface;
use Dandelion\Operation\ResultInterface;
use Dandelion\Operation\SplitRepositoryInitializerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

class SplitRepositoryInitAllCommandTest extends Unit
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
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Operation\SplitRepositoryInitializerInterface
     */
    protected $initializerMock;

    /**
     * @var \Dandelion\Console\Command\SplitRepositoryInitAllCommand
     */
    protected $splitRepositoryInitAllCommand;

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

        $this->initializerMock = $this->getMockBuilder(SplitRepositoryInitializerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->splitRepositoryInitAllCommand = new SplitRepositoryInitAllCommand($this->initializerMock);
    }

    /**
     * @return void
     */
    public function testGetName(): void
    {
        static::assertEquals(
            SplitRepositoryInitAllCommand::NAME,
            $this->splitRepositoryInitAllCommand->getName()
        );
    }

    /**
     * @return void
     */
    public function testGetDescription(): void
    {
        static::assertEquals(
            SplitRepositoryInitAllCommand::DESCRIPTION,
            $this->splitRepositoryInitAllCommand->getDescription()
        );
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testRun(): void
    {
        $messageText = 'Lorem ipsum';

        $this->initializerMock->expects(static::atLeastOnce())
            ->method('executeForAllRepositories')
            ->with(
                static::callback(
                    static function (array $arguments) {
                        return count($arguments) === 0;
                    }
                )
            )->willReturn($this->resultMock);

        $this->resultMock->expects(static::atLeastOnce())
            ->method('getMessages')
            ->willReturn($this->messageMocks);

        $this->messageMocks[0]->expects(static::atLeastOnce())
            ->method('getType')
            ->willReturn(MessageInterface::TYPE_INFO);

        $this->messageMocks[0]->expects(static::atLeastOnce())
            ->method('getText')
            ->willReturn($messageText);

        $this->outputMock->expects(static::atLeastOnce())
            ->method('writeln')
            ->withConsecutive(
                ['Init split repositories:'],
                ['---------------------------------'],
                [sprintf('<fg=green>✔</> %s', $messageText)],
                ['---------------------------------'],
                ['Finished']
            );

        static::assertEquals(0, $this->splitRepositoryInitAllCommand->run($this->inputMock, $this->outputMock));
    }
}
