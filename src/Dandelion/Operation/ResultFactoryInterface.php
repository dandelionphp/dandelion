<?php

declare(strict_types=1);

namespace Dandelion\Operation;

interface ResultFactoryInterface
{
    /**
     * @return \Dandelion\Operation\ResultInterface
     */
    public function create(): ResultInterface;
}
