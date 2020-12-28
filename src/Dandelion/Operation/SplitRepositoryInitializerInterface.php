<?php

declare(strict_types=1);

namespace Dandelion\Operation;

interface SplitRepositoryInitializerInterface extends OperationInterface
{
    /**
     * @param string $repositoryName
     *
     * @return \Dandelion\Operation\SplitRepositoryInitializerInterface
     */
    public function executeForSingleRepository(string $repositoryName): SplitRepositoryInitializerInterface;
}
