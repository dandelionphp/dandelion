<?php

declare(strict_types=1);

namespace Dandelion\Configuration;

class Vcs
{
    /**
     * @var string
     */
    protected $owner;

    /**
     * @var string
     */
    protected $token;

    /**
     * @return string
     */
    public function getOwner(): string
    {
        return $this->owner;
    }

    /**
     * @param string $owner
     *
     * @return \Dandelion\Configuration\Vcs
     */
    public function setOwner(string $owner): Vcs
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
