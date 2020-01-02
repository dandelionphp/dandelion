<?php

namespace DR\Monorepo\VersionControl;

interface SplitshLiteInterface
{
    /**
     * @param string $pathToPackage
     *
     * @return string
     */
    public function getSha1(string $pathToPackage): string;
}