<?php

namespace Dandelion\Configuration;

use Dandelion\Exception\ConfigurationNotValidException;
use Swaggest\JsonSchema\InvalidValue;
use Swaggest\JsonSchema\SchemaContract;

use function json_decode;

class ConfigurationValidator implements ConfigurationValidatorInterface
{
    /**
     * @var \Dandelion\Configuration\ConfigurationLoaderInterface
     */
    protected $configurationLoader;

    /**
     * @var \Swaggest\JsonSchema\SchemaContract
     */
    protected $jsonSchema;

    /**
     * @param \Dandelion\Configuration\ConfigurationLoaderInterface $configurationLoader
     * @param \Swaggest\JsonSchema\SchemaContract $jsonSchema
     */
    public function __construct(
        ConfigurationLoaderInterface $configurationLoader,
        SchemaContract $jsonSchema
    ) {
        $this->configurationLoader = $configurationLoader;
        $this->jsonSchema = $jsonSchema;
    }

    /**
     * @return \Dandelion\Configuration\ConfigurationValidatorInterface
     *
     * @throws \Dandelion\Exception\ConfigurationNotValidException
     */
    public function validate(): ConfigurationValidatorInterface
    {
        $configurationFileContent = $this->configurationLoader->loadRaw();

        try {
            $this->jsonSchema->in(json_decode($configurationFileContent));
        } catch (InvalidValue $e) {
            throw new ConfigurationNotValidException($e->getMessage());
        }

        return $this;
    }
}
