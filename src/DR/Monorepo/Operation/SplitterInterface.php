<?php


namespace DR\Monorepo\Operation;

interface SplitterInterface
{
    /**
     * @param string $pathToPackage
     * @param string $repository
     * @param string $branch
     *
     * @return \DR\Monorepo\Operation\SplitterInterface
     */
    public function split(string $pathToPackage, string $repository, string $branch): SplitterInterface;

    /**
     * @param string $branch
     *
     * @return \DR\Monorepo\Operation\SplitterInterface
     */
    public function splitAll(string $branch = 'master'): SplitterInterface;
}