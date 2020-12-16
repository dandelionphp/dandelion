<?php

declare(strict_types=1);

namespace Dandelion\Operation;

interface OperationInterface
{
    /**
     * @param string[] $arguments
     *
     * @return \Dandelion\Operation\ResultInterface
     */
    public function executeForAllRepositories(array $arguments = []): ResultInterface;
}
