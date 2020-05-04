<?php

declare(strict_types=1);

namespace Dandelion\Configuration;

use Codeception\Test\Unit;
use Dandelion\Filesystem\FilesystemInterface;
use Exception;
use ReflectionProperty;
use SplFileInfo;
use Symfony\Component\Serializer\SerializerInterface;

use function get_class;

class ConfigurationLoaderTest extends Unit
{
    /**
     * @var \Dandelion\Configuration\ConfigurationLoaderInterface
     */
    protected $configurationLoader;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Configuration\ConfigurationFinderInterface
     */
    protected $configurationFinderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\SplFileInfo
     */
    protected $splFileInfoMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Filesystem\FilesystemInterface
     */
    protected $filesystemMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Serializer\SerializerInterface
     */
    protected $symfonySerializerMock;

    protected function _before(): void
    {
        parent::_before();

        $this->splFileInfoMock = $this->getMockBuilder(SplFileInfo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurationFinderMock = $this->getMockBuilder(ConfigurationFinderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystemMock = $this->getMockBuilder(FilesystemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->symfonySerializerMock = $this->getMockBuilder(SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurationLoader = new ConfigurationLoader(
            $this->configurationFinderMock,
            $this->filesystemMock,
            $this->symfonySerializerMock
        );
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testLoad(): void
    {
        $configurationFileContent = '{...}';
        $pathToConfigurationFile = '/path/to/dandelion.json';

        $configurationMock = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurationFinderMock->expects($this->atLeastOnce())
            ->method('find')
            ->willReturn($this->splFileInfoMock);

        $this->splFileInfoMock->expects($this->atLeastOnce())
            ->method('getRealPath')
            ->willReturn($pathToConfigurationFile);

        $this->filesystemMock->expects($this->atLeastOnce())
            ->method('readFile')
            ->with($pathToConfigurationFile)
            ->willReturn($configurationFileContent);

        $this->symfonySerializerMock->expects($this->atLeastOnce())
            ->method('deserialize')
            ->with($configurationFileContent, Configuration::class, 'json')
            ->willReturn($configurationMock);

        $this->assertEquals($configurationMock, $this->configurationLoader->load());
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testLoadAfterFirstCall(): void
    {
        $configurationMock = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configurationProperty = new ReflectionProperty(get_class($this->configurationLoader), 'configuration');
        $configurationProperty->setAccessible(true);
        $configurationProperty->setValue($this->configurationLoader, $configurationMock);

        $this->configurationFinderMock->expects($this->never())
            ->method('find')
            ->willReturn($this->splFileInfoMock);

        $this->splFileInfoMock->expects($this->never())
            ->method('getRealPath');

        $this->filesystemMock->expects($this->never())
            ->method('readFile');

        $this->symfonySerializerMock->expects($this->never())
            ->method('deserialize');

        $this->assertEquals($configurationMock, $this->configurationLoader->load());
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testLoadWithUnsupportedConfigurationFile(): void
    {
        $configurationFileContent = '[...]';
        $pathToConfigurationFile = '/path/to/dandelion.json';

        $this->configurationFinderMock->expects($this->atLeastOnce())
            ->method('find')
            ->willReturn($this->splFileInfoMock);

        $this->splFileInfoMock->expects($this->atLeastOnce())
            ->method('getRealPath')
            ->willReturn($pathToConfigurationFile);

        $this->filesystemMock->expects($this->atLeastOnce())
            ->method('readFile')
            ->with($pathToConfigurationFile)
            ->willReturn($configurationFileContent);

        $this->symfonySerializerMock->expects($this->atLeastOnce())
            ->method('deserialize')
            ->with($configurationFileContent, Configuration::class, 'json')
            ->willReturn([]);

        try {
            $this->configurationLoader->load();
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testLoadRaw(): void
    {
        $configurationFileContent = '{...}';
        $pathToConfigurationFile = '/path/to/dandelion.json';

        $this->configurationFinderMock->expects($this->atLeastOnce())
            ->method('find')
            ->willReturn($this->splFileInfoMock);

        $this->splFileInfoMock->expects($this->atLeastOnce())
            ->method('getRealPath')
            ->willReturn($pathToConfigurationFile);

        $this->filesystemMock->expects($this->atLeastOnce())
            ->method('readFile')
            ->with($pathToConfigurationFile)
            ->willReturn($configurationFileContent);

        $this->assertEquals($configurationFileContent, $this->configurationLoader->loadRaw());
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testLoadRawAfterFirstCall(): void
    {
        $rawConfiguration = '{...}';

        $rawConfigurationProperty = new ReflectionProperty(get_class($this->configurationLoader), 'rawConfiguration');
        $rawConfigurationProperty->setAccessible(true);
        $rawConfigurationProperty->setValue($this->configurationLoader, $rawConfiguration);

        $this->configurationFinderMock->expects($this->never())
            ->method('find');

        $this->splFileInfoMock->expects($this->never())
            ->method('getRealPath');

        $this->filesystemMock->expects($this->never())
            ->method('readFile');

        $this->assertEquals($rawConfiguration, $this->configurationLoader->loadRaw());
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testLoadRawWithNonExistingConfigurationFile(): void
    {
        $this->configurationFinderMock->expects($this->atLeastOnce())
            ->method('find')
            ->willReturn($this->splFileInfoMock);

        $this->splFileInfoMock->expects($this->atLeastOnce())
            ->method('getRealPath')
            ->willReturn(false);

        $this->filesystemMock->expects($this->never())
            ->method('readFile');

        try {
            $this->configurationLoader->loadRaw();
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }
}
