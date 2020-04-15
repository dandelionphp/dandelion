<?php

declare(strict_types=1);

namespace Dandelion\Operation\Result;

class MessageFactory implements MessageFactoryInterface
{
    /**
     * @return \Dandelion\Operation\Result\MessageInterface
     */
    public function create(): MessageInterface
    {
        return new Message();
    }
}
