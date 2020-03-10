<?php

namespace Dandelion\Console\Command;

use Codeception\Test\Unit;
use Dandelion\Operation\ReleaserInterface;
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
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Operation\ReleaserInterface
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

        $this->releaserMock = $this->getMockBuilder(ReleaserInterface::class)
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
        $version = '1.0.0';
        $repositoryName = 'package';

        $this->inputMock->expects($this->atLeastOnce())
            ->method('getArgument')
            ->withConsecutive(['repositoryName'], ['branch'], ['version'])
            ->willReturnOnConsecutiveCalls(
                $repositoryName, $branch, $version
            );

        $this->releaserMock->expects($this->atLeastOnce())
            ->method('release')
            ->with($repositoryName, $branch, $version);

        $this->assertEquals(0, $this->releaseCommand->run($this->inputMock, $this->outputMock));
    }

    /**
     * @return void
     */
    public function testRunWithInvalidArgument(): void
    {
        $branch = 1;
        $version = '1.0.0';
        $repositoryName = 'package';

        $this->inputMock->expects($this->atLeastOnce())
            ->method('getArgument')
            ->withConsecutive(['repositoryName'], ['branch'], ['version'])
            ->willReturnOnConsecutiveCalls(
                $repositoryName, $branch, $version
            );

        $this->releaserMock->expects($this->never())
            ->method('release');

        try {
            $this->releaseCommand->run($this->inputMock, $this->outputMock);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }
}
