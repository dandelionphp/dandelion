<?php

namespace DR\Monorepo;

use DR\Monorepo\Configuration\ConfigurationFinder;
use DR\Monorepo\Configuration\ConfigurationLoader;
use DR\Monorepo\Console\Command\SplitAllCommand;
use DR\Monorepo\Console\Command\SplitCommand;
use DR\Monorepo\Environment\OperatingSystem;
use DR\Monorepo\Filesystem\Filesystem;
use DR\Monorepo\Operation\Splitter;
use DR\Monorepo\Process\ProcessFactory;
use DR\Monorepo\VersionControl\Git;
use DR\Monorepo\VersionControl\SplitshLite;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use function sprintf;

class MonorepoServiceProvider implements ServiceProviderInterface
{
    /**
     * @param \Pimple\Container $container
     */
    public function register(Container $container): void
    {
        $container = $this->registerDirectoryPaths($container);
        $container = $this->registerFilesystem($container);
        $container = $this->registerFinder($container);
        $container = $this->registerSerializer($container);
        $container = $this->registerConfigurationLoader($container);
        $container = $this->registerConfigurationFinder($container);
        $container = $this->registerProcessFactory($container);
        $container = $this->registerOperatingSystem($container);
        $container = $this->registerGit($container);
        $container = $this->registerSplitshLite($container);
        $container = $this->registerSplitter($container);

        $this->registerCommands($container);
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Pimple\Container
     */
    protected function registerDirectoryPaths(Container $container): Container
    {
        $rootDir = sprintf('%s/../../../', rtrim(__DIR__, '/'));

        $container->offsetSet('root_dir', static function () use ($rootDir) {
            return $rootDir;
        });

        $container->offsetSet('src_dir', static function () use ($rootDir) {
            return sprintf('%ssrc/', $rootDir);
        });

        $container->offsetSet('bin_dir', static function () use ($rootDir) {
            return sprintf('%sbin/', $rootDir);
        });

        return $container;
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Pimple\Container
     */
    protected function registerCommands(Container $container): Container {
        $self = $this;

        $container->offsetSet('commands', static function (Container $container) use ($self) {
            return [
                $self->createSplitCommand($container),
                $self->createSplitAllCommand($container),
            ];
        });

        return $container;
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \DR\Monorepo\Console\Command\SplitCommand
     */
    protected function createSplitCommand(Container $container): SplitCommand
    {
        return new SplitCommand($container->offsetGet('splitter'));
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \DR\Monorepo\Console\Command\SplitAllCommand
     */
    protected function createSplitAllCommand(Container $container): SplitAllCommand
    {
        return new SplitAllCommand($container->offsetGet('splitter'));
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Pimple\Container
     */
    protected function registerSplitter(Container $container): Container
    {
        $container->offsetSet('splitter', static function (Container $container) {
           return new Splitter(
               $container->offsetGet('configuration_loader'),
               $container->offsetGet('process_factory'),
               $container->offsetGet('git'),
               $container->offsetGet('splitsh_lite'),
               $container->offsetGet('bin_dir')
           );
        });

        return $container;
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Pimple\Container
     */
    protected function registerGit(Container $container): Container
    {
        $container->offsetSet('git', static function (Container $container) {
            return new Git($container->offsetGet('process_factory'));
        });

        return $container;
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Pimple\Container
     */
    protected function registerProcessFactory(Container $container): Container
    {
        $container->offsetSet('process_factory', static function() {
            return new ProcessFactory();
        });

        return $container;
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Pimple\Container
     */
    protected function registerSplitshLite(Container $container): Container
    {
        $container->offsetSet('splitsh_lite', static function(Container $container) {
            return new SplitshLite(
                $container->offsetGet('operating_system'),
                $container->offsetGet('process_factory'),
                $container->offsetGet('bin_dir')
            );
        });

        return $container;
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Pimple\Container
     */
    protected function registerOperatingSystem(Container $container): Container
    {
        $container->offsetSet('operating_system', static function(Container $container) {
            return new OperatingSystem();
        });

        return $container;
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Pimple\Container
     */
    protected function registerConfigurationLoader(Container $container): Container
    {
        $container->offsetSet('configuration_loader', static function(Container $container) {
            return new ConfigurationLoader(
                $container->offsetGet('configuration_finder'),
                $container->offsetGet('filesystem'),
                $container->offsetGet('serializer')
            );
        });

        return $container;
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Pimple\Container
     */
    protected function registerConfigurationFinder(Container $container): Container
    {
        $container->offsetSet('configuration_finder', static function(Container $container) {
            return new ConfigurationFinder($container->offsetGet('finder'));
        });

        return $container;
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Pimple\Container
     */
    protected function registerFinder(Container $container): Container
    {
        $container->offsetSet('finder', static function() {
            return new Finder();
        });

        return $container;
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Pimple\Container
     */
    protected function registerFilesystem(Container $container): Container
    {
        $container->offsetSet('filesystem', static function() {
            return new Filesystem();
        });

        return $container;
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Pimple\Container
     */
    protected function registerSerializer(Container $container): Container
    {
        $container->offsetSet('serializer', static function() {
            $normalizer = [
                new ObjectNormalizer(null, null, null, new PhpDocExtractor()),
                new ArrayDenormalizer()
            ];

            return new Serializer(
                $normalizer,
                [new JsonEncoder()]
            );
        });

        return $container;
    }
}
