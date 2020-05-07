<?php

namespace Dandelion\Operation;

interface InitializerInterface
{
    /**
     * @return \Dandelion\Operation\ResultInterface
     */
    public function addGitRemotes(): ResultInterface;
}
