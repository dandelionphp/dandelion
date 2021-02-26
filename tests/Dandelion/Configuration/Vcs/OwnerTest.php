<?php

namespace Dandelion\Configuration\Vcs;

use Codeception\Test\Unit;

class OwnerTest extends Unit
{
    /**
     * @var \Dandelion\Configuration\Vcs\Owner
     */
    protected $owner;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->owner = new Owner();
    }

    /**
     * @return void
     */
    public function testSetAndGetName(): void
    {
        $name = 'foo';

        static::assertEquals($this->owner, $this->owner->setName($name));
        static::assertEquals($name, $this->owner->getName());
    }

    /**
     * @return void
     */
    public function testSetAndGetType(): void
    {
        $type = 'organisation';

        static::assertEquals($this->owner, $this->owner->setType($type));
        static::assertEquals($type, $this->owner->getType());
    }
}
