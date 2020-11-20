<?php

declare(strict_types=1);

namespace Dandelion\Filesystem;

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
     * @param string $path
     * @param int $mode
     *
     * @return \Dandelion\Filesystem\FilesystemInterface
     */
    public function createDirectory(string $path, int $mode = 0755): FilesystemInterface;

    /**
     * @param string $directory
     *
     * @return \Dandelion\Filesystem\FilesystemInterface
     */
    public function changeDirectory(string $directory): FilesystemInterface;

    /**
     * @param string $directory
     *
     * @return \Dandelion\Filesystem\FilesystemInterface
     */
    public function removeDirectory(string $directory): FilesystemInterface;

    /**
     * @param string $file
     *
     * @return \Dandelion\Filesystem\FilesystemInterface
     *
     * @throws \Exception
     */
    public function removeFile(string $file): FilesystemInterface;
}
