<?php

declare(strict_types=1);

namespace Dandelion\Operation;

interface ReleaserInterface extends OperationInterface
{
    /**
     * @param string $repositoryName
     * @param string $branch
     *
     * @return \Dandelion\Operation\ReleaserInterface
     */
    public function executeForSingleRepository(string $repositoryName, string $branch): ReleaserInterface;
}
