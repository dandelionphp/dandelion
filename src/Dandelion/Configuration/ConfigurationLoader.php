<?php

declare(strict_types=1);

namespace Dandelion\Configuration;

use Dandelion\Exception\InvalidTypeException;
use Dandelion\Exception\IOException;
use Dandelion\Filesystem\FilesystemInterface;
use Symfony\Component\Serializer\SerializerInterface;

use function is_object;

class ConfigurationLoader implements ConfigurationLoaderInterface
{
    /**
     * @var \Dandelion\Configuration\ConfigurationFinderInterface
     */
    protected $configurationFinder;

    /**
     * @var \Dandelion\Filesystem\FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var \Symfony\Component\Serializer\SerializerInterface
     */
    protected $serializer;

    /**
     * @param \Dandelion\Configuration\ConfigurationFinderInterface $configurationFinder
     * @param \Dandelion\Filesystem\FilesystemInterface $filesystem
     * @param \Symfony\Component\Serializer\SerializerInterface $serializer
     */
    public function __construct(
        ConfigurationFinderInterface $configurationFinder,
        FilesystemInterface $filesystem,
        SerializerInterface $serializer
    ) {
        $this->configurationFinder = $configurationFinder;
        $this->filesystem = $filesystem;
        $this->serializer = $serializer;
    }

    /**
     * @return \Dandelion\Configuration\Configuration
     *
     * @throws \Dandelion\Exception\InvalidTypeException
     * @throws \Dandelion\Exception\IOException
     */
    public function load(): Configuration
    {
        $configurationFile = $this->configurationFinder->find();
        $realPathToConfigurationFile = $configurationFile->getRealPath();

        if ($realPathToConfigurationFile === false) {
            throw new IOException('Configuration file does not exists.');
        }

        $configurationFileContent = $this->filesystem->readFile($realPathToConfigurationFile);

        $configuration = $this->serializer->deserialize(
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
