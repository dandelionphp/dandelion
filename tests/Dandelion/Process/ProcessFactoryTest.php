<?php

declare(strict_types=1);

namespace Dandelion\Process;

use Codeception\Test\Unit;
use Symfony\Component\Process\Process;

class ProcessFactoryTest extends Unit
{
    /**
     * @var \Dandelion\Process\ProcessFactory
     */
    protected $processFactory;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->processFactory = new ProcessFactory();
    }

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $process = $this->processFactory->create(['ls', '-la']);
        $this->assertInstanceOf(Process::class, $process);
    }
}