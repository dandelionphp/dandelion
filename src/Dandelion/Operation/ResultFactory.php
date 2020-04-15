<?php

declare(strict_types=1);

namespace Dandelion\Operation;

class ResultFactory implements ResultFactoryInterface
{
    /**
     * @return \Dandelion\Operation\ResultInterface
     */
    public function create(): ResultInterface
    {
        return new Result();
    }
}
