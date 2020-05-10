<?php

declare(strict_types=1);

namespace Dandelion;

use Codeception\Test\Unit;
use Dandelion\Console\Command\ReleaseAllCommand;
use Dandelion\Console\Command\ReleaseCommand;
use Dandelion\Console\Command\SplitAllCommand;
use Dandelion\Console\Command\SplitCommand;
use Dandelion\Console\Command\ValidateCommand;
use Pimple\Container;

use function codecept_root_dir;
use function sprintf;

class DandelionServiceProviderTest extends Unit
{
    /**
     * @var \Dandelion\DandelionServiceProvider
     */
    protected $monorepoServiceProvider;

    /**
     * @var \Pimple\Container
     */
    protected $container;

    /**
     * @var string
     */
    protected $expectedRootDir;


    /**
     * @var string
     */
    protected $expectedResourcesDir;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->expectedRootDir = codecept_root_dir('src/Dandelion/../../');
        $this->expectedResourcesDir = sprintf('%sresources%s', $this->expectedRootDir, DIRECTORY_SEPARATOR);

        $this->container = new Container();

        $this->monorepoServiceProvider = new DandelionServiceProvider();
    }

    /**
     * @return void
     */
    public function testRegister(): void
    {
        $this->monorepoServiceProvider->register($this->container);

        $this->assertTrue($this->container->offsetExists('root_dir'));
        $this->assertEquals($this->expectedRootDir, $this->container->offsetGet('root_dir'));

        $this->assertTrue($this->container->offsetExists('resources_dir'));
        $this->assertEquals($this->expectedResourcesDir, $this->container->offsetGet('resources_dir'));

        $this->assertTrue($this->container->offsetExists('commands'));
        $this->assertCount(5, $this->container->offsetGet('commands'));
        $this->assertInstanceOf(SplitCommand::class, $this->container->offsetGet('commands')[0]);
        $this->assertInstanceOf(SplitAllCommand::class, $this->container->offsetGet('commands')[1]);
        $this->assertInstanceOf(ReleaseCommand::class, $this->container->offsetGet('commands')[2]);
        $this->assertInstanceOf(ReleaseAllCommand::class, $this->container->offsetGet('commands')[3]);
        $this->assertInstanceOf(ValidateCommand::class, $this->container->offsetGet('commands')[4]);
    }
}
