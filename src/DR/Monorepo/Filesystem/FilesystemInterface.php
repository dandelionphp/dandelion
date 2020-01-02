<?php

namespace DR\Monorepo\Filesystem;

interface FilesystemInterface
{
    /**
     * @param string $pathToFile
     *
     * @return string
     */
    public function readFile(string $pathToFile): string;
}
