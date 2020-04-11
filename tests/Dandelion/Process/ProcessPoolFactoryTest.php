<?php

declare(strict_types=1);

namespace Dandelion\Process;

use Codeception\Test\Unit;
use Psr\Log\LoggerInterface;

class ProcessPoolFactoryTest extends Unit
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
     * @var \Dandelion\Process\ProcessPoolFactoryInterface
     */
    protected $processPoolFactory;

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

        $this->processPoolFactory = new ProcessPoolFactory(
            $this->processFactoryMock,
            $this->loggerMock
        );
    }

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $processPool = $this->processPoolFactory->create();

        $this->assertInstanceOf(ProcessPool::class, $processPool);
    }
}
