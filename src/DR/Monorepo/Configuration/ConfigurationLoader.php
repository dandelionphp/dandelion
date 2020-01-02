<?php

namespace DR\Monorepo\Configuration;

use DR\Monorepo\Exception\InvalidTypeException;
use DR\Monorepo\Exception\IOException;
use DR\Monorepo\Filesystem\FilesystemInterface;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;
use function is_object;

class ConfigurationLoader implements ConfigurationLoaderInterface
{
    /**
     * @var \DR\Monorepo\Configuration\ConfigurationFinderInterface
     */
    protected $configurationFinder;

    /**
     * @var \DR\Monorepo\Filesystem\FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var \Symfony\Component\Serializer\Serializer
     */
    protected $symfonySerializer;

    /**
     * @param \DR\Monorepo\Configuration\ConfigurationFinderInterface $configurationFinder
     * @param \DR\Monorepo\Filesystem\FilesystemInterface $filesystem
     * @param \Symfony\Component\Serializer\Serializer $symfonySerializer
     */
    public function __construct(
        ConfigurationFinderInterface $configurationFinder,
        FilesystemInterface $filesystem,
        SymfonySerializer $symfonySerializer
    ) {
        $this->configurationFinder = $configurationFinder;
        $this->filesystem = $filesystem;
        $this->symfonySerializer = $symfonySerializer;
    }

    /**
     * @return \DR\Monorepo\Configuration\ConfigurationInterface
     *
     * @throws \DR\Monorepo\Exception\InvalidTypeException
     * @throws \DR\Monorepo\Exception\IOException
     */
    public function load(): ConfigurationInterface
    {
        $configurationFile = $this->configurationFinder->find();
        $realPathToConfigurationFile = $configurationFile->getRealPath();

        if ($realPathToConfigurationFile === false) {
            throw new IOException('Configuration file does not exists.');
        }

        $configurationFileContent = $this->filesystem->readFile($realPathToConfigurationFile);

        $configuration = $this->symfonySerializer->deserialize(
            $configurationFileContent,
            Configuration::class,
            'json'
        );

        if (!is_object($configuration) || !($configuration instanceof Configuration)) {
            throw new InvalidTypeException('Invalid type of deserialized data.');
        }

        return $configuration;
    }
}
