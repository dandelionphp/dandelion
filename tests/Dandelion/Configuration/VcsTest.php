<?php

namespace Dandelion\Configuration;

use Codeception\Test\Unit;

class VcsTest extends Unit
{
    /**
     * @var \Dandelion\Configuration\Vcs
     */
    protected $vcs;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->vcs = new Vcs();
    }

    /**
     * @return void
     */
    public function testSetAndGetOwner(): void
    {
        $owner = 'foo';

        static::assertEquals($this->vcs, $this->vcs->setOwner($owner));
        static::assertEquals($owner, $this->vcs->getOwner());
    }

    /**
     * @return void
     */
    public function testSetAndGetToken(): void
    {
        $token = 'foo-token-0123';

        static::assertEquals($this->vcs, $this->vcs->setToken($token));
        static::assertEquals($token, $this->vcs->getToken());
    }
}
