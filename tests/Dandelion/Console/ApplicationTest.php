<?php

declare(strict_types=1);

namespace Dandelion\Console;

use Codeception\Test\Unit;
use Pimple\Container;
use stdClass;
use Symfony\Component\Console\Command\Command;

class ApplicationTest extends Unit
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Pimple\Container
     */
    protected $containerMock;

    /**
     * @var \Dandelion\Console\Application
     */
    protected $application;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->containerMock = $this->getMockBuilder(Container::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->application = new Application($this->containerMock);
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testAllWithoutAdditionalDefaultCommands(): void
    {
        $this->containerMock->expects($this->atLeastOnce())
            ->method('offsetExists')
            ->with('commands')
            ->willReturn(false);

        $this->containerMock->expects($this->never())
            ->method('offsetGet')
            ->with('commands');

        $this->assertCount(2, $this->application->all());
    }
    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testAllWithEmptyAdditionalDefaultCommands(): void
    {
        $this->containerMock->expects($this->atLeastOnce())
            ->method('offsetExists')
            ->with('commands')
            ->willReturn(true);

        $this->containerMock->expects($this->atLeastOnce())
            ->method('offsetGet')
            ->with('commands')
            ->willReturn(0);

        $this->assertCount(2, $this->application->all());
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testAll(): void
    {
        $this->containerMock->expects($this->atLeastOnce())
            ->method('offsetExists')
            ->with('commands')
            ->willReturn(true);

        $this->containerMock->expects($this->atLeastOnce())
            ->method('offsetGet')
            ->with('commands')
            ->willReturn([new Command('foo:bar'), new stdClass()]);

        $this->assertCount(3, $this->application->all());
    }
}
