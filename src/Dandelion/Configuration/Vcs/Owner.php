<?php

declare(strict_types=1);

namespace Dandelion\Configuration\Vcs;

class Owner
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $name;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return \Dandelion\Configuration\Vcs\Owner
     */
    public function setType(string $type): Owner
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return \Dandelion\Configuration\Vcs\Owner
     */
    public function setName(string $name): Owner
    {
        $this->name = $name;

        return $this;
    }
}
