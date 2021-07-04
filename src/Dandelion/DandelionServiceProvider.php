<?php

declare(strict_types=1);

namespace Dandelion;

use Dandelion\Configuration\ConfigurationFinder;
use Dandelion\Configuration\ConfigurationLoader;
use Dandelion\Configuration\ConfigurationValidator;
use Dandelion\Console\Command\MergeCommand;
use Dandelion\Console\Command\SplitRepositoryInitAllCommand;
use Dandelion\Console\Command\SplitRepositoryInitCommand;
use Dandelion\Console\Command\ReleaseAllCommand;
use Dandelion\Console\Command\ReleaseCommand;
use Dandelion\Console\Command\SplitAllCommand;
use Dandelion\Console\Command\SplitCommand;
use Dandelion\Console\Command\ValidateCommand;
use Dandelion\Filesystem\Filesystem;
use Dandelion\Merger\Merger;
use Dandelion\Merger\Resources\Collection;
use Dandelion\Merger\Resources\ComposerJsonResource;
use Dandelion\Operation\SplitRepositoryInitializer;
use Dandelion\Operation\Releaser;
use Dandelion\Operation\Result\MessageFactory;
use Dandelion\Operation\ResultFactory;
use Dandelion\Operation\Splitter;
use Dandelion\Process\ProcessFactory;
use Dandelion\Process\ProcessPoolFactory;
use Dandelion\VersionControl\Git;
use Dandelion\VersionControl\Platform\GithubFactory;
use Dandelion\VersionControl\SplitshLite;
use GuzzleHttp\Client;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Swaggest\JsonSchema\Schema;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\SemaphoreStore;
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
        $container = $this->registerFilesystem($container);
        $container = $this->registerFinder($container);
        $container = $this->registerSerializer($container);
        $container = $this->registerConfigurationLoader($container);
        $container = $this->registerConfigurationFinder($container);
        $container = $this->registerLockStore($container);
        $container = $this->registerLockFactory($container);
        $container = $this->registerProcessFactory($container);
        $container = $this->registerProcessPoolFactory($container);
        $container = $this->registerResultFactory($container);
        $container = $this->registerMessageFactory($container);
        $container = $this->registerPlatformFactory($container);
        $container = $this->registerGit($container);
        $container = $this->registerSplitshLite($container);
        $container = $this->registerSplitRepositoryInitializer($container);
        $container = $this->registerSplitter($container);
        $container = $this->registerReleaser($container);
        $container = $this->registerConfigurationValidator($container);
        $container = $this->registerMergerResourceCollection($container);
        $container = $this->registerMerger($container);
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

        $container->offsetSet('resources_dir', static function () use ($rootDir) {
            return sprintf('%sresources%s', $rootDir, DIRECTORY_SEPARATOR);
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
                $self->createSplitRepositoryInitCommand($container),
                $self->createSplitRepositoryInitAllCommand($container),
                $self->createSplitCommand($container),
                $self->createSplitAllCommand($container),
                $self->createReleaseCommand($container),
                $self->createReleaseAllCommand($container),
                $self->createValidateCommand($container),
                $self->createMergeCommand($container),
            ];
        });

        return $container;
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Dandelion\Console\Command\SplitRepositoryInitCommand
     */
    protected function createSplitRepositoryInitCommand(Container $container): SplitRepositoryInitCommand
    {
        return new SplitRepositoryInitCommand($container->offsetGet('split_repository_initializer'));
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Dandelion\Console\Command\SplitRepositoryInitAllCommand
     */
    protected function createSplitRepositoryInitAllCommand(Container $container): SplitRepositoryInitAllCommand
    {
        return new SplitRepositoryInitAllCommand($container->offsetGet('split_repository_initializer'));
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
    protected function registerSplitRepositoryInitializer(Container $container): Container
    {
        $container->offsetSet('split_repository_initializer', static function (Container $container) {
            return new SplitRepositoryInitializer(
                $container->offsetGet('configuration_loader'),
                $container->offsetGet('process_pool_factory'),
                $container->offsetGet('result_factory'),
                $container->offsetGet('message_factory'),
                $container->offsetGet('platform_factory')
            );
        });

        return $container;
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
                $container->offsetGet('process_pool_factory'),
                $container->offsetGet('result_factory'),
                $container->offsetGet('message_factory'),
                $container->offsetGet('platform_factory'),
                $container->offsetGet('git'),
                $container->offsetGet('splitsh_lite'),
                $container->offsetGet('lock_factory')
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
     * @return \Pimple\Container
     */
    protected function registerLockStore(Container $container): Container
    {
        $container->offsetSet('lock_store', static function () {
            return new SemaphoreStore();
        });

        return $container;
    }

    /**
     * @param \Pimple\Container $container
     * @return \Pimple\Container
     */
    protected function registerLockFactory(Container $container): Container
    {
        $container->offsetSet('lock_factory', static function (Container $container) {
            return new LockFactory(
                $container->offsetGet('lock_store')
            );
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
    protected function registerProcessPoolFactory(Container $container): Container
    {
        $container->offsetSet('process_pool_factory', static function (Container $container) {
            return new ProcessPoolFactory(
                $container->offsetGet('process_factory')
            );
        });

        return $container;
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Pimple\Container
     */
    protected function registerResultFactory(Container $container): Container
    {
        $container->offsetSet('result_factory', static function () {
            return new ResultFactory();
        });

        return $container;
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Pimple\Container
     */
    protected function registerMessageFactory(Container $container): Container
    {
        $container->offsetSet('message_factory', static function () {
            return new MessageFactory();
        });

        return $container;
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Pimple\Container
     */
    protected function registerPlatformFactory(Container $container): Container
    {
        $container->offsetSet('platform_factory', static function () {
            return new GithubFactory(
                new Client()
            );
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
                $container->offsetGet('process_factory'),
                $container->offsetGet('configuration_loader')
            );
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
                $container->offsetGet('process_pool_factory'),
                $container->offsetGet('result_factory'),
                $container->offsetGet('message_factory'),
                $container->offsetGet('platform_factory'),
                $container->offsetGet('git')
            );
        });

        return $container;
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Pimple\Container
     */
    protected function registerMergerResourceCollection(Container $container): Container
    {
        $container->offsetSet('merger_resource_collection', static function (Container $container) {
            return new Collection([
                new ComposerJsonResource($container->offsetGet('filesystem'))
            ]);
        });

        return $container;
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Pimple\Container
     */
    protected function registerMerger(Container $container): Container
    {
        $container->offsetSet('merger', static function (Container $container) {
            return new Merger(
                $container->offsetGet('finder'),
                $container->offsetGet('merger_resource_collection'),
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
            $container->offsetGet('configuration_validator')
        );
    }

    /**
     * @param \Pimple\Container $container
     *
     * @return \Dandelion\Console\Command\MergeCommand
     */
    protected function createMergeCommand(Container $container): MergeCommand
    {
        return new MergeCommand(
            $container->offsetGet('merger')
        );
    }
}
