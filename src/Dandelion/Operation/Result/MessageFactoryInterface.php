<?php

declare(strict_types=1);

namespace Dandelion\Operation\Result;

interface MessageFactoryInterface
{
    /**
     * @return \Dandelion\Operation\Result\MessageInterface
     */
    public function create(): MessageInterface;
}
