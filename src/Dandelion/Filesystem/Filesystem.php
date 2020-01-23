<?php

declare(strict_types=1);

namespace Dandelion\Filesystem;

use Dandelion\Exception\IOException;

use function chdir;
use function file_get_contents;
use function getcwd;
use function in_array;
use function is_file;
use function rmdir;
use function rtrim;
use function scandir;
use function sprintf;
use function unlink;

class Filesystem implements FilesystemInterface
{
    protected const IGNORED_DIRECTORY_ENTRIES = ['.', '..'];

    /**
     * @param string $pathToFile
     *
     * @return string
     *
     * @throws \Dandelion\Exception\IOException
     */
    public function readFile(string $pathToFile): string
    {
        $fileContent = @file_get_contents($pathToFile);

        if ($fileContent === false) {
            throw new IOException(sprintf('Failed to read file "%s".', $pathToFile), 0, null);
        }

        return $fileContent;
    }

    /**
     * @return string
     *
     * @throws \Dandelion\Exception\IOException
     */
    public function getCurrentWorkingDirectory(): string
    {
        $currentWorkingDirectory = getcwd();

        if (!$currentWorkingDirectory) {
            throw new IOException(sprintf('Could not get current working directory.'));
        }

        return rtrim($currentWorkingDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * @param string $directory
     *
     * @return \Dandelion\Filesystem\FilesystemInterface
     *
     * @throws \Dandelion\Exception\IOException
     */
    public function changeDirectory(string $directory): FilesystemInterface
    {
        if (!@chdir($directory)) {
            throw new IOException(sprintf('Could not change directory.'));
        }

        return $this;
    }

    /**
     * @param string $directory
     *
     * @return \Dandelion\Filesystem\FilesystemInterface
     *
     * @throws \Exception
     */
    public function removeDirectory(string $directory): FilesystemInterface
    {
        if (!is_dir($directory)) {
            throw new IOException(sprintf('%s is not a directory.', $directory));
        }

        $this->removeDirectoryContent($directory);

        if (!rmdir($directory)) {
            throw new IOException(sprintf('Could not delete directory "%s".', $directory));
        }

        return $this;
    }

    /**
     * @param string $directory
     *
     * @return \Dandelion\Filesystem\FilesystemInterface
     *
     * @throws \Exception
     */
    protected function removeDirectoryContent(string $directory): FilesystemInterface
    {
        $directoryEntries = @scandir($directory);

        if ($directoryEntries === false) {
            throw new IOException(sprintf('Could not scan directory "%s".', $directory));
        }

        foreach ($directoryEntries as $directoryEntry) {
            if (in_array($directoryEntry, static::IGNORED_DIRECTORY_ENTRIES, true)) {
                continue;
            }

            $pathToDirectoryEntry = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $directoryEntry;

            if (is_file($pathToDirectoryEntry)) {
                $this->removeFile($pathToDirectoryEntry);
                continue;
            }

            if (is_dir($pathToDirectoryEntry)) {
                $this->removeDirectory($pathToDirectoryEntry);
                continue;
            }
        }

        return $this;
    }

    /**
     * @param string $file
     *
     * @return \Dandelion\Filesystem\FilesystemInterface
     *
     * @throws \Exception
     */
    public function removeFile(string $file): FilesystemInterface
    {
        if (!is_file($file)) {
            throw new IOException(sprintf('%s is not a file.', $file));
        }

        if (!unlink($file)) {
            throw new IOException(sprintf('Could not delete file "%s".', $file));
        }

        return $this;
    }
}
