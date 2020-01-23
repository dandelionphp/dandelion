<?php

declare(strict_types=1);

namespace Dandelion\Environment;

interface OperatingSystemInterface
{
    /**
     * @return string
     */
    public function getFamily(): string;

    /**
     * @return string
     */
    public function getMachineType(): string;
}
