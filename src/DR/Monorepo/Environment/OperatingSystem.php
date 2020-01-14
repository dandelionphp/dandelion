<?php

namespace DR\Monorepo\Environment;

use function php_uname;

class OperatingSystem implements OperatingSystemInterface
{
    public const MACHINE_TYPE_X86_64 = 'x86_64';

    public const FAMILY_DARWIN = 'Darwin';
    public const FAMILY_LINUX = 'Linux';

    /**
     * @return string
     */
    public function getFamily(): string
    {
        return PHP_OS_FAMILY;
    }

    /**
     * @return string
     */
    public function getMachineType(): string
    {
        return php_uname('m');
    }
}