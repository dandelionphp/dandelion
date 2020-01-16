<?php

declare(strict_types=1);

namespace DR\Monorepo\Filesystem;

use DR\Monorepo\Exception\IOException;
use function chdir;
use function file_get_contents;
use function getcwd;
use function in_array;
use function is_file;
use function rmdir;
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

    /**
     * @return string
     */
    public function getCurrentWorkingDirectory(): string
    {
        return getcwd();
    }

    /**
     * @inheritDoc
     */
    public function changeDirectory(string $directory): FilesystemInterface
    {
        if (!chdir($directory)) {
            new IOException(sprintf('Could not change directory.'));
        }

        return $this;
    }

    /**
     * @param string $directory
     *
     * @param bool $recursive
     * @return \DR\Monorepo\Filesystem\FilesystemInterface
     *
     * @throws \Exception
     */
    public function removeDirectory(string $directory, $recursive = false): FilesystemInterface
    {
        if (!is_dir($directory)) {
            throw new IOException(sprintf('%s is not a directory.', $directory));
        }

        if ($recursive) {
            $this->removeDirectoryContent($directory);
        }

        if (!rmdir($directory)) {
            throw new IOException('Could not delete directory "%s".', $directory);
        }

        return $this;
    }

    /**
     * @param string $directory
     *
     * @return \DR\Monorepo\Filesystem\FilesystemInterface
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

            if (is_file($directoryEntry)) {
                $this->removeFile($directoryEntry);
                continue;
            }

            if (is_dir($directoryEntry)) {
                $this->removeDirectory($directoryEntry, true);
                continue;
            }
        }

        return $this;
    }

    /**
     * @param string $file
     *
     * @return \DR\Monorepo\Filesystem\FilesystemInterface
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
