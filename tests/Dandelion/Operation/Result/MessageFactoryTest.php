<?php

declare(strict_types=1);

namespace Dandelion\Operation\Result;

use Codeception\Test\Unit;

class MessageFactoryTest extends Unit
{
    /**
     * @var \Dandelion\Operation\Result\MessageFactoryInterface
     */
    protected $messageFactory;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->messageFactory = new MessageFactory();
    }

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $message = $this->messageFactory->create();
        $this->assertInstanceOf(Message::class, $message);
    }
}
