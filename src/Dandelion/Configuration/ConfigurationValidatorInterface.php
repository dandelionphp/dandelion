<?php

declare(strict_types=1);

namespace Dandelion\Configuration;

interface ConfigurationValidatorInterface
{
    /**
     * @return \Dandelion\Configuration\ConfigurationValidatorInterface
     */
    public function validate(): ConfigurationValidatorInterface;
}
