<?php

namespace DR\Monorepo\Filesystem;

use DR\Monorepo\Exception\IOException;
use function file_get_contents;
use function sprintf;

class Filesystem implements FilesystemInterface
{
    /**
     * @param string $pathToFile
     *
     * @return string
     *
     * @throws \DR\Monorepo\Exception\IOException
     */
    public function readFile(string $pathToFile): string
    {
        $fileContent = @file_get_contents($pathToFile);

        if ($fileContent === false) {
            throw new IOException(sprintf('Failed to read file "%s".', $pathToFile), 0, null);
        }

        return $fileContent;
    }
}
