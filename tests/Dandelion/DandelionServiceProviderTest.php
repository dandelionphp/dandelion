<?php

declare(strict_types=1);

namespace Dandelion;

use Codeception\Test\Unit;
use Dandelion\Console\Command\SplitRepositoryInitAllCommand;
use Dandelion\Console\Command\SplitRepositoryInitCommand;
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

        static::assertTrue($this->container->offsetExists('root_dir'));
        static::assertEquals($this->expectedRootDir, $this->container->offsetGet('root_dir'));

        static::assertTrue($this->container->offsetExists('resources_dir'));
        static::assertEquals($this->expectedResourcesDir, $this->container->offsetGet('resources_dir'));

        static::assertTrue($this->container->offsetExists('commands'));
        static::assertCount(7, $this->container->offsetGet('commands'));
        static::assertInstanceOf(SplitRepositoryInitCommand::class, $this->container->offsetGet('commands')[0]);
        static::assertInstanceOf(SplitRepositoryInitAllCommand::class, $this->container->offsetGet('commands')[1]);
        static::assertInstanceOf(SplitCommand::class, $this->container->offsetGet('commands')[2]);
        static::assertInstanceOf(SplitAllCommand::class, $this->container->offsetGet('commands')[3]);
        static::assertInstanceOf(ReleaseCommand::class, $this->container->offsetGet('commands')[4]);
        static::assertInstanceOf(ReleaseAllCommand::class, $this->container->offsetGet('commands')[5]);
        static::assertInstanceOf(ValidateCommand::class, $this->container->offsetGet('commands')[6]);
    }
}
