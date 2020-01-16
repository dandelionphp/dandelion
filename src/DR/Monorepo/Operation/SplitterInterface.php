<?php


namespace DR\Monorepo\Operation;

interface SplitterInterface
{
    /**
     * @param string $repositoryName
     * @param string $branch
     *
     * @return \DR\Monorepo\Operation\SplitterInterface
     */
    public function split(string $repositoryName, string $branch): SplitterInterface;

    /**
     * @param string $branch
     *
     * @return \DR\Monorepo\Operation\SplitterInterface
     */
    public function splitAll(string $branch = 'master'): SplitterInterface;
}