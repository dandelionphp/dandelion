<?php

declare(strict_types=1);

namespace DR\Monorepo\Filesystem;

interface FilesystemInterface
{
    /**
     * @param string $pathToFile
     *
     * @return string
     */
    public function readFile(string $pathToFile): string;

    /**
     * @return string
     */
    public function getCurrentWorkingDirectory(): string;

    /**
     * @param string $directory
     *
     * @return \DR\Monorepo\Filesystem\FilesystemInterface
     */
    public function changeDirectory(string $directory): FilesystemInterface;

    /**
     * @param string $directory
     * @param bool $recursive
     *
     * @return \DR\Monorepo\Filesystem\FilesystemInterface
     */
    public function removeDirectory(string $directory, $recursive = false): FilesystemInterface;

    /**
     * @param string $file
     *
     * @return \DR\Monorepo\Filesystem\FilesystemInterface
     *
     * @throws \Exception
     */
    public function removeFile(string $file): FilesystemInterface;
}
