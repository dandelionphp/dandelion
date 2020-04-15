<?php

declare(strict_types=1);

namespace Dandelion\Operation\Result;

class Message implements MessageInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $text;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return \Dandelion\Operation\Result\MessageInterface
     */
    public function setType(string $type): MessageInterface
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return \Dandelion\Operation\Result\MessageInterface
     */
    public function setText(string $text): MessageInterface
    {
        $this->text = $text;

        return $this;
    }
}
