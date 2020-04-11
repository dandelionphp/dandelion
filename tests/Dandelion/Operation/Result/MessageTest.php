<?php

declare(strict_types=1);

namespace Dandelion\Operation\Result;

use Codeception\Test\Unit;

class MessageTest extends Unit
{
    /**
     * @var \Dandelion\Operation\Result\MessageInterface
     */
    protected $message;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->message = new Message();
    }

    /**
     * @return void
     */
    public function testSetAndGetType(): void
    {
        $this->assertEquals(
            $this->message,
            $this->message->setType(MessageInterface::TYPE_ERROR)
        );

        $this->assertEquals(
            MessageInterface::TYPE_ERROR,
            $this->message->getType()
        );
    }

    /**
     * @return void
     */
    public function testSetAndGetText(): void
    {
        $text = 'Lorem ipsum';

        $this->assertEquals(
            $this->message,
            $this->message->setText($text)
        );

        $this->assertEquals(
            $text,
            $this->message->getText()
        );
    }
}
