<?php

declare(strict_types=1);

namespace Dandelion\Operation;

use Dandelion\Operation\Result\MessageInterface;

interface ResultInterface
{
    /**
     * @param \Dandelion\Operation\Result\MessageInterface $message
     *
     * @return \Dandelion\Operation\ResultInterface
     */
    public function addMessage(MessageInterface $message): ResultInterface;

    /**
     * @return \Dandelion\Operation\Result\MessageInterface[]
     */
    public function getMessages(): array;

    /**
     * @param \Dandelion\Operation\Result\MessageInterface[] $messages
     *
     * @return \Dandelion\Operation\ResultInterface
     */
    public function setMessages(array $messages): ResultInterface;
}
