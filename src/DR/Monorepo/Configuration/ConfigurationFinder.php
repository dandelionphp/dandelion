<?php

namespace DR\Monorepo\Configuration;

use DR\Monorepo\Exception\ConfigurationFileNotFoundException;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Finder\Finder as SymfonyFinder;
use function getcwd;

class ConfigurationFinder implements ConfigurationFinderInterface
{
    /**
     * @var \Symfony\Component\Finder\Finder
     */
    protected $symfonyFinder;

    /**
     * @param \Symfony\Component\Finder\Finder $symfonyFinder
     */
    public function __construct(SymfonyFinder $symfonyFinder)
    {
        $this->symfonyFinder = $symfonyFinder;
    }


    /**
     * @return \SplFileInfo
     *
     * @throws \DR\Monorepo\Exception\ConfigurationFileNotFoundException
     * @throws \Exception
     */
    public function find(): SplFileInfo
    {
        $this->symfonyFinder->files()
            ->in(getcwd())
            ->name('monorepo.json')
            ->depth('== 0');

        if ($this->symfonyFinder->count() !== 1) {
            throw new ConfigurationFileNotFoundException('Configuration "monorepo.json" not found.');
        }

        $iterator = $this->symfonyFinder->getIterator();
        $iterator->rewind();

        if (!$iterator->valid()) {
            throw new RuntimeException('Current position is not valid.');
        }

        return $iterator->current();
    }
}
