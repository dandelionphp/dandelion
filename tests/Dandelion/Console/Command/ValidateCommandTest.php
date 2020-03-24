<?php

namespace Dandelion\Console\Command;

use Codeception\Test\Unit;
use Dandelion\Configuration\ConfigurationValidatorInterface;
use Dandelion\Exception\ConfigurationNotValidException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateCommandTest extends Unit
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
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Configuration\ConfigurationValidatorInterface
     */
    protected $configurationValidatorMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Log\LoggerInterface
     */
    protected $loggerMock;

    /**
     * @var \Dandelion\Console\Command\ValidateCommand
     */
    protected $validateCommand;

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

        $this->configurationValidatorMock = $this->getMockBuilder(ConfigurationValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->validateCommand = new ValidateCommand(
            $this->configurationValidatorMock,
            $this->loggerMock
        );
    }

    /**
     * @return void
     */
    public function testGetName(): void
    {
        $this->assertEquals(ValidateCommand::NAME, $this->validateCommand->getName());
    }

    /**
     * @return void
     */
    public function testGetDescription(): void
    {
        $this->assertEquals(ValidateCommand::DESCRIPTION, $this->validateCommand->getDescription());
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testRun(): void
    {
        $this->configurationValidatorMock->expects($this->atLeastOnce())
            ->method('validate');

        $this->loggerMock->expects($this->atLeastOnce())
            ->method('notice')
            ->with('Configuration is valid.');

        $this->assertEquals(0, $this->validateCommand->run($this->inputMock, $this->outputMock));
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testRunWithInvalidConfiguration(): void
    {
        $this->configurationValidatorMock->expects($this->atLeastOnce())
            ->method('validate')
            ->willThrowException(new ConfigurationNotValidException('...'));

        $this->loggerMock->expects($this->atLeastOnce())
            ->method('error')
            ->with('...');

        $this->assertEquals(1, $this->validateCommand->run($this->inputMock, $this->outputMock));
    }
}
