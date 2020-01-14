<?php

namespace DR\Monorepo\Console\Command;

use Codeception\Test\Unit;
use DR\Monorepo\Operation\SplitterInterface;
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
     * @var \PHPUnit\Framework\MockObject\MockObject|\DR\Monorepo\Operation\SplitterInterface
     */
    protected $splitterMock;

    /**
     * @var \DR\Monorepo\Console\Command\SplitCommand
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
        $pathToPackage = '/path/to/package';
        $repository = 'git@github.com:user/package.git';
        $branch = 'master';

        $this->inputMock->expects($this->atLeastOnce())
            ->method('getArgument')
            ->withConsecutive(['pathToPackage'], ['repository'], ['branch'])
            ->willReturnOnConsecutiveCalls($pathToPackage, $repository, $branch);

        $this->splitterMock->expects($this->atLeastOnce())
            ->method('split')
            ->with($pathToPackage, $repository, $branch);

        $this->assertEquals(null, $this->splitCommand->run($this->inputMock, $this->outputMock));
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testRunWithInvalidArgument(): void
    {
        $pathToPackage = '/path/to/package';
        $repository = 'git@github.com:user/package.git';
        $branch = 1;

        $this->inputMock->expects($this->atLeastOnce())
            ->method('getArgument')
            ->withConsecutive(['pathToPackage'], ['repository'], ['branch'])
            ->willReturnOnConsecutiveCalls($pathToPackage, $repository, $branch);

        $this->splitterMock->expects($this->never())
            ->method('split');

        try {
            $this->splitCommand->run($this->inputMock, $this->outputMock);
            $this->fail();
        } catch (Exception $e) {}
    }
}