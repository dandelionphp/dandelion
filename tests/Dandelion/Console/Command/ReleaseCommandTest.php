<?php

declare(strict_types=1);

namespace Dandelion\Console\Command;

use Codeception\Test\Unit;
use Dandelion\Operation\AbstractOperation;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReleaseCommandTest extends Unit
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
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Operation\AbstractOperation
     */
    protected $releaserMock;

    /**
     * @var \Dandelion\Console\Command\ReleaseCommand
     */
    protected $releaseCommand;

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

        $this->releaserMock = $this->getMockBuilder(AbstractOperation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->releaseCommand = new ReleaseCommand($this->releaserMock);
    }

    /**
     * @return void
     */
    public function testGetName(): void
    {
        $this->assertEquals(ReleaseCommand::NAME, $this->releaseCommand->getName());
    }

    /**
     * @return void
     */
    public function testGetDescription(): void
    {
        $this->assertEquals(ReleaseCommand::DESCRIPTION, $this->releaseCommand->getDescription());
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testRun(): void
    {
        $branch = 'master';
        $repositoryName = 'package';

        $this->inputMock->expects($this->atLeastOnce())
            ->method('getArgument')
            ->withConsecutive(['repositoryName'], ['branch'])
            ->willReturnOnConsecutiveCalls(
                $repositoryName,
                $branch
            );

        $this->releaserMock->expects($this->atLeastOnce())
            ->method('executeForSingleRepository')
            ->with($repositoryName, $branch);

        $this->assertEquals(0, $this->releaseCommand->run($this->inputMock, $this->outputMock));
    }

    /**
     * @return void
     */
    public function testRunWithInvalidArgument(): void
    {
        $branch = 1;
        $repositoryName = 'package';

        $this->inputMock->expects($this->atLeastOnce())
            ->method('getArgument')
            ->withConsecutive(['repositoryName'], ['branch'])
            ->willReturnOnConsecutiveCalls(
                $repositoryName,
                $branch
            );

        $this->releaserMock->expects($this->never())
            ->method('executeForSingleRepository');

        try {
            $this->releaseCommand->run($this->inputMock, $this->outputMock);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }
}
