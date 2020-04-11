<?php

declare(strict_types=1);

namespace Dandelion\Operation;

use Codeception\Test\Unit;

class ResultFactoryTest extends Unit
{
    /**
     * @var \Dandelion\Operation\ResultFactoryInterface
     */
    protected $resultFactory;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->resultFactory = new ResultFactory();
    }

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $result = $this->resultFactory->create();
        $this->assertInstanceOf(Result::class, $result);
    }
}
