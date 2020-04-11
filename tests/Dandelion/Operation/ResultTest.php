<?php

declare(strict_types=1);

namespace Dandelion\Operation;

use Codeception\Test\Unit;
use Dandelion\Operation\Result\MessageInterface;

class ResultTest extends Unit
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Operation\Result\MessageInterface
     */
    protected $messageMock;

    /**
     * @var \Dandelion\Operation\ResultInterface
     */
    protected $result;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->messageMock = $this->getMockBuilder(MessageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->result = new Result();
    }

    /**
     * @return void
     */
    public function testAddMessage(): void
    {
        $this->assertEquals(
            $this->result,
            $this->result->addMessage($this->messageMock)
        );
    }

    /**
     * @return void
     */
    public function testSetAndGetMessages(): void
    {
        $messageMocks = [$this->messageMock];

        $this->assertEquals(
            $this->result,
            $this->result->setMessages($messageMocks)
        );

        $this->assertEquals(
            $messageMocks,
            $this->result->getMessages()
        );
    }
}
