<?php

declare(strict_types=1);

namespace Dandelion\Operation;

use Dandelion\Operation\Result\MessageInterface;

class Result implements ResultInterface
{
    /**
     * @var \Dandelion\Operation\Result\MessageInterface[]
     */
    protected $messages = [];

    /**
     * @param \Dandelion\Operation\Result\MessageInterface $message
     *
     * @return \Dandelion\Operation\ResultInterface
     */
    public function addMessage(MessageInterface $message): ResultInterface
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     * @return \Dandelion\Operation\Result\MessageInterface[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @param \Dandelion\Operation\Result\MessageInterface[] $messages
     *
     * @return \Dandelion\Operation\ResultInterface
     */
    public function setMessages(array $messages): ResultInterface
    {
        $this->messages = $messages;

        return $this;
    }
}
