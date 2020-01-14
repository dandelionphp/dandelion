<?php

namespace DR\Monorepo\Configuration;

use Codeception\Test\Unit;
use DR\Monorepo\Filesystem\FilesystemInterface;
use Exception;
use SplFileInfo;
use Symfony\Component\Serializer\SerializerInterface;

class ConfigurationLoaderTest extends Unit
{
    /**
     * @var \DR\Monorepo\Configuration\ConfigurationLoader
     */
    protected $configurationLoader;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\DR\Monorepo\Configuration\ConfigurationFinderInterface
     */
    protected $configurationFinderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\SplFileInfo
     */
    protected $splFileInfoMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\DR\Monorepo\Filesystem\FilesystemInterface
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
        $pathToConfigurationFile = '/path/to/monorepo.json';

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
    public function testLoadWithNonExistingConfigurationFile(): void
    {
        $this->configurationFinderMock->expects($this->atLeastOnce())
            ->method('find')
            ->willReturn($this->splFileInfoMock);

        $this->splFileInfoMock->expects($this->atLeastOnce())
            ->method('getRealPath')
            ->willReturn(false);

        $this->filesystemMock->expects($this->never())
            ->method('readFile');

        $this->symfonySerializerMock->expects($this->never())
            ->method('deserialize');

        try {
            $this->configurationLoader->load();
            $this->fail();
        } catch (Exception $e) {}
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testLoadWithUnsupportedConfigurationFile(): void
    {
        $configurationFileContent = '[...]';
        $pathToConfigurationFile = '/path/to/monorepo.json';

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
            $this->fail();
        } catch (Exception $e) {}
    }
}