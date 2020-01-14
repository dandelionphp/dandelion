<?php

namespace DR\Monorepo\Environment;

use Codeception\Test\Unit;
use function php_uname;

class OperatingSystemTest extends Unit
{
    /**
     * @var \DR\Monorepo\Environment\OperatingSystem
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