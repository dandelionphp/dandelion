<?php

declare(strict_types=1);

namespace Dandelion\Configuration;

use Dandelion\Configuration\Vcs\Owner;

class Vcs
{
    /**
     * @var \Dandelion\Configuration\Vcs\Owner
     */
    protected $owner;

    /**
     * @var string
     */
    protected $token;

    /**
     * @return \Dandelion\Configuration\Vcs\Owner
     */
    public function getOwner(): Owner
    {
        return $this->owner;
    }

    /**
     * @param \Dandelion\Configuration\Vcs\Owner $owner
     *
     * @return \Dandelion\Configuration\Vcs
     */
    public function setOwner(Owner $owner): Vcs
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return \Dandelion\Configuration\Vcs
     */
    public function setToken(string $token): Vcs
    {
        $this->token = $token;

        return $this;
    }
}
