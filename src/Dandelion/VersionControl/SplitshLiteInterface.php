<?php

declare(strict_types=1);

namespace Dandelion\VersionControl;

interface SplitshLiteInterface
{
    /**
     * @param string $pathToPackage
     *
     * @return string
     */
    public function getSha1(string $pathToPackage): string;
}
