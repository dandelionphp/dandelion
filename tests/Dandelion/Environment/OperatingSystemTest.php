<?php

declare(strict_types=1);

namespace Dandelion\Environment;

use Codeception\Test\Unit;
use function php_uname;

class OperatingSystemTest extends Unit
{
    /**
     * @var \Dandelion\Environment\OperatingSystemInterface
     */
    protected $operatingSystem;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->operatingSystem = new OperatingSystem();
    }

    /**
     * @return void
     */
    public function testGetFamily(): void
    {
        $this->assertEquals(PHP_OS_FAMILY, $this->operatingSystem->getFamily());
    }

    /**
     * @return void
     */
    public function testGetMachineType(): void
    {
        $this->assertEquals(php_uname('m'), $this->operatingSystem->getMachineType());
    }
}