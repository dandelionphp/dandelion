<?php

declare(strict_types=1);

namespace Dandelion\Operation;

interface SplitterInterface extends OperationInterface
{
    /**
     * @param string $repositoryName
     * @param string $branch
     *
     * @return \Dandelion\Operation\SplitterInterface
     */
    public function executeForSingleRepository(string $repositoryName, string $branch): SplitterInterface;
}
