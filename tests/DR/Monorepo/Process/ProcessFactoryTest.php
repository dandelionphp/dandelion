<?php

namespace DR\Monorepo\Process;

use Codeception\Test\Unit;
use Symfony\Component\Process\Process;

class ProcessFactoryTest extends Unit
{
    /**
     * @var \DR\Monorepo\Process\ProcessFactory
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