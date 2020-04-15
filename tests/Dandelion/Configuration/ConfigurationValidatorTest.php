<?php

declare(strict_types=1);

namespace Dandelion\Configuration;

use Codeception\Test\Unit;
use Dandelion\Exception\ConfigurationNotValidException;
use Swaggest\JsonSchema\InvalidValue;
use Swaggest\JsonSchema\SchemaContract;

use function json_decode;

class ConfigurationValidatorTest extends Unit
{
    /**
     * @var \Dandelion\Configuration\ConfigurationValidatorInterface
     */
    protected $configurationValidator;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Configuration\ConfigurationLoaderInterface
     */
    protected $configurationLoaderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Swaggest\JsonSchema\SchemaContract
     */
    protected $schemaContractMock;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->configurationLoaderMock = $this->getMockBuilder(ConfigurationLoaderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->schemaContractMock = $this->getMockBuilder(SchemaContract::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurationValidator = new ConfigurationValidator(
            $this->configurationLoaderMock,
            $this->schemaContractMock
        );
    }

    /**
     * @return void
     */
    public function testValidate(): void
    {
        $rawConfiguration = '{}';

        $this->configurationLoaderMock->expects($this->atLeastOnce())
            ->method('loadRaw')
            ->willReturn($rawConfiguration);

        $this->schemaContractMock->expects($this->atLeastOnce())
            ->method('in')
            ->with(json_decode($rawConfiguration));

        $this->assertEquals($this->configurationValidator, $this->configurationValidator->validate());
    }

    /**
     * @return void
     */
    public function testValidateWithInvalidConfiguration(): void
    {
        $rawConfiguration = '{}';

        $this->configurationLoaderMock->expects($this->atLeastOnce())
            ->method('loadRaw')
            ->willReturn($rawConfiguration);

        $this->schemaContractMock->expects($this->atLeastOnce())
            ->method('in')
            ->with(json_decode($rawConfiguration))
            ->willThrowException(new InvalidValue('...'));

        try {
            $this->configurationValidator->validate();
            $this->fail();
        } catch (ConfigurationNotValidException $e) {
        }
    }
}
