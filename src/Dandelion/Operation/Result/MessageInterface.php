<?php

declare(strict_types=1);

namespace Dandelion\Operation\Result;

interface MessageInterface
{
    public const TYPE_INFO = 'info';
    public const TYPE_ERROR = 'error';

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $type
     *
     * @return \Dandelion\Operation\Result\MessageInterface
     */
    public function setType(string $type): MessageInterface;

    /**
     * @return string
     */
    public function getText(): string;

    /**
     * @param string $text
     * @return \Dandelion\Operation\Result\MessageInterface
     */
    public function setText(string $text): MessageInterface;
}
