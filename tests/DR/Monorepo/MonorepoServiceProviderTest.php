<?php

namespace DR\Monorepo;

use Codeception\Test\Unit;
use DR\Monorepo\Console\Command\SplitAllCommand;
use DR\Monorepo\Console\Command\SplitCommand;
use Pimple\Container;
use function codecept_root_dir;

class MonorepoServiceProviderTest extends Unit
{
    /**
     * @var \DR\Monorepo\MonorepoServiceProvider
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

        $this->expectedRootDir = codecept_root_dir('src/DR/Monorepo/../../../');
        $this->expectedSrcDir = \sprintf('%ssrc/',$this->expectedRootDir);
        $this->expectedBinDir = \sprintf('%sbin/',$this->expectedRootDir);

        $this->container = new Container();

        $this->monorepoServiceProvider = new MonorepoServiceProvider();
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
        $this->assertCount(2, $this->container->offsetGet('commands'));
        $this->assertInstanceOf(SplitCommand::class, $this->container->offsetGet('commands')[0]);
        $this->assertInstanceOf(SplitAllCommand::class, $this->container->offsetGet('commands')[1]);
    }
}
