<?php

namespace Dandelion\Configuration;

interface ConfigurationValidatorInterface
{
    /**
     * @return \Dandelion\Configuration\ConfigurationValidatorInterface
     */
    public function validate(): ConfigurationValidatorInterface;
}
