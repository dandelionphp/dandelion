<?php

declare(strict_types=1);

namespace Dandelion\Operation;

interface SplitterInterface
{
    /**
     * @param string $repositoryName
     * @param string $branch
     *
     * @return \Dandelion\Operation\SplitterInterface
     */
    public function split(string $repositoryName, string $branch): SplitterInterface;

    /**
     * @param string $branch
     *
     * @return \Dandelion\Operation\SplitterInterface
     */
    public function splitAll(string $branch = 'master'): SplitterInterface;
}
