<?php

declare(strict_types=1);

namespace Dandelion\Operation;

interface InitializerInterface extends OperationInterface
{
    /**
     * @param string $repositoryName
     *
     * @return \Dandelion\Operation\InitializerInterface
     */
    public function executeForSingleRepository(string $repositoryName): InitializerInterface;
}
