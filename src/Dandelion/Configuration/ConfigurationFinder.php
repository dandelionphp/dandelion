<?php

declare(strict_types=1);

namespace Dandelion\Configuration;

use Dandelion\Exception\ConfigurationFileNotFoundException;
use Dandelion\Exception\RuntimeException;
use Dandelion\Filesystem\FilesystemInterface;
use SplFileInfo;
use Symfony\Component\Finder\Finder as SymfonyFinder;

class ConfigurationFinder implements ConfigurationFinderInterface
{
    /**
     * @var \Symfony\Component\Finder\Finder
     */
    protected $symfonyFinder;

    /**
     * @var \Dandelion\Filesystem\FilesystemInterface
     */
    protected $filesystem;

    /**
     * @param \Symfony\Component\Finder\Finder $symfonyFinder
     * @param \Dandelion\Filesystem\FilesystemInterface $filesystem
     */
    public function __construct(
        SymfonyFinder $symfonyFinder,
        FilesystemInterface $filesystem
    ) {
        $this->symfonyFinder = $symfonyFinder;
        $this->filesystem = $filesystem;
    }

    /**
     * @return \SplFileInfo
     *
     * @throws \Dandelion\Exception\ConfigurationFileNotFoundException
     * @throws \Exception
     */
    public function find(): SplFileInfo
    {
        $this->symfonyFinder->files()
            ->in($this->filesystem->getCurrentWorkingDirectory())
            ->name('dandelion.json')
            ->depth('== 0');

        if ($this->symfonyFinder->count() !== 1) {
            throw new ConfigurationFileNotFoundException('Configuration "dandelion.json" not found.');
        }

        $iterator = $this->symfonyFinder->getIterator();
        $iterator->rewind();

        if (!$iterator->valid()) {
            throw new RuntimeException('Current position is not valid.');
        }

        return $iterator->current();
    }
}
