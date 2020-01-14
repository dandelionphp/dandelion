<?php

namespace DR\Monorepo\Environment;

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