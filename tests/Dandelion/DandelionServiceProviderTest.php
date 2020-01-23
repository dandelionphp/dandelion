<?php

namespace Dandelion;

use Codeception\Test\Unit;
use Dandelion\Console\Command\ReleaseAllCommand;
use Dandelion\Console\Command\ReleaseCommand;
use Dandelion\Console\Command\SplitAllCommand;
use Dandelion\Console\Command\SplitCommand;
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
    protected $expectedSrcDir;

    /**
     * @var string
     */
    protected $expectedBinDir;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->expectedRootDir = codecept_root_dir('src/Dandelion/../../../');
        $this->expectedSrcDir = sprintf('%ssrc%s',$this->expectedRootDir, DIRECTORY_SEPARATOR);
        $this->expectedBinDir = sprintf('%sbin%s',$this->expectedRootDir, DIRECTORY_SEPARATOR);

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

        $this->assertTrue($this->container->offsetExists('src_dir'));
        $this->assertEquals($this->expectedSrcDir, $this->container->offsetGet('src_dir'));

        $this->assertTrue($this->container->offsetExists('bin_dir'));
        $this->assertEquals($this->expectedBinDir, $this->container->offsetGet('bin_dir'));

        $this->assertTrue($this->container->offsetExists('commands'));
        $this->assertCount(4, $this->container->offsetGet('commands'));
        $this->assertInstanceOf(SplitCommand::class, $this->container->offsetGet('commands')[0]);
        $this->assertInstanceOf(SplitAllCommand::class, $this->container->offsetGet('commands')[1]);
        $this->assertInstanceOf(ReleaseCommand::class, $this->container->offsetGet('commands')[2]);
        $this->assertInstanceOf(ReleaseAllCommand::class, $this->container->offsetGet('commands')[3]);
    }
}
