<?php

declare(strict_types=1);

namespace Dandelion;

use Dandelion\Configuration\ConfigurationFinder;
use Dandelion\Configuration\ConfigurationLoader;
use Dandelion\Configuration\ConfigurationValidator;
use Dandelion\Console\Command\ReleaseAllCommand;
use Dandelion\Console\Command\ReleaseCommand;
use Dandelion\Console\Command\SplitAllCommand;
use Dandelion\Console\Command\SplitCommand;
use Dandelion\Console\Command\ValidateCommand;
use Dandelion\Environment\OperatingSystem;
use Dandelion\Filesystem\Filesystem;
use Dandelion\Operation\Releaser;
use Dandelion\Operation\Splitter;
use Dandelion\Process\ProcessFactory;
use Dandelion\VersionControl\Git;
use Dandelion\VersionControl\SplitshLite;
use Monolog\Logger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Swaggest\JsonSchema\Schema;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Finder\Finder;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

use function rtrim;
use function sprintf;
use function str_repeat;

class DandelionServiceProvider implements ServiceProviderInterface
{
    /**
     * @param \Pimple\Container $container
     */
    public function register(Container $container): void
    {
        $container = $this->registerDirectoryPaths($container);
        $container = $this->registerLogger($container);
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
        $container = $this->registerReleaser($container);
        $container = $this->registerConfigurationValidator($container);
        $container = $this->registerConsoleOutput($container);
        $this->registerCommands($container);
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Pimple\Container
     */
    protected function registerDirectoryPaths(Container $container): Container
    {
        $rootDir = rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
            . str_repeat('..' . DIRECTORY_SEPARATOR, 2);

        $container->offsetSet('root_dir', static function () use ($rootDir) {
            return $rootDir;
        });

        $container->offsetSet('src_dir', static function () use ($rootDir) {
            return sprintf('%ssrc%s', $rootDir, DIRECTORY_SEPARATOR);
        });

        $container->offsetSet('resources_dir', static function () use ($rootDir) {
            return sprintf('%sresources%s', $rootDir, DIRECTORY_SEPARATOR);
        });

        $container->offsetSet('bin_dir', static function () use ($rootDir) {
            return sprintf('%sbin%s', $rootDir, DIRECTORY_SEPARATOR);
        });

        return $container;
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Pimple\Container
     */
    protected function registerLogger(Container $container): Container
    {
        $container->offsetSet('logger', static function (Container $container) {
            return new Logger('main', [
                new ConsoleHandler($container->offsetGet('console_output'), true, [])
            ]);
        });

        return $container;
    }


    /**
     * @param \Pimple\Container $container
     *
     * @return \Pimple\Container
     */
    protected function registerConsoleOutput(Container $container): Container
    {
        $container->offsetSet('console_output', static function () {
            return new ConsoleOutput();
        });

        return $container;
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Pimple\Container
     */
    protected function registerCommands(Container $container): Container
    {
        $self = $this;

        $container->offsetSet('commands', static function (Container $container) use ($self) {
            return [
                $self->createSplitCommand($container),
                $self->createSplitAllCommand($container),
                $self->createReleaseCommand($container),
                $self->createReleaseAllCommand($container),
                $self->createValidateCommand($container),
            ];
        });

        return $container;
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Dandelion\Console\Command\SplitCommand
     */
    protected function createSplitCommand(Container $container): SplitCommand
    {
        return new SplitCommand($container->offsetGet('splitter'));
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Dandelion\Console\Command\SplitAllCommand
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
        $container->offsetSet('process_factory', static function () {
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
        $container->offsetSet('splitsh_lite', static function (Container $container) {
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
        $container->offsetSet('operating_system', static function () {
            return new OperatingSystem();
        });

        return $container;
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Pimple\Container
     */
    protected function registerConfigurationValidator(Container $container): Container
    {
        $container->offsetSet('configuration_validator', static function (Container $container) {
            $pathToDandelionSchema = sprintf('%sdandelion.schema.json', $container->offsetGet('resources_dir'));

            return new ConfigurationValidator(
                $container->offsetGet('configuration_loader'),
                Schema::import($pathToDandelionSchema)
            );
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
        $container->offsetSet('configuration_loader', static function (Container $container) {
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
        $container->offsetSet('configuration_finder', static function (Container $container) {
            return new ConfigurationFinder(
                $container->offsetGet('finder'),
                $container->offsetGet('filesystem')
            );
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
        $container->offsetSet('finder', static function () {
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
        $container->offsetSet('filesystem', static function () {
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
        $container->offsetSet('serializer', static function () {
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

    /**
     * @param \Pimple\Container $container
     *
     * @return \Pimple\Container
     */
    protected function registerReleaser(Container $container): Container
    {
        $container->offsetSet('releaser', static function (Container $container) {
            return new Releaser(
                $container->offsetGet('configuration_loader'),
                $container->offsetGet('filesystem'),
                $container->offsetGet('process_factory'),
                $container->offsetGet('git'),
                $container->offsetGet('bin_dir')
            );
        });

        return $container;
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Dandelion\Console\Command\ReleaseCommand
     */
    protected function createReleaseCommand(Container $container): ReleaseCommand
    {
        return new ReleaseCommand($container->offsetGet('releaser'));
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Dandelion\Console\Command\ReleaseAllCommand
     */
    protected function createReleaseAllCommand(Container $container): ReleaseAllCommand
    {
        return new ReleaseAllCommand($container->offsetGet('releaser'));
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Dandelion\Console\Command\ValidateCommand
     */
    protected function createValidateCommand(Container $container): ValidateCommand
    {
        return new ValidateCommand(
            $container->offsetGet('configuration_validator'),
            $container->offsetGet('logger')
        );
    }
}
