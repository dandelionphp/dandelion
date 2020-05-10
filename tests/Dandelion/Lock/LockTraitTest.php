<?php

declare(strict_types=1);

namespace Dandelion\Lock;

use Codeception\Test\Unit;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

class LockTraitTest extends Unit
{
    use LockTrait;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->lockFactory = null;
    }


    /**
     * @return void
     */
    public function testAcquireAndReleaseWithNullLockFactory(): void
    {
        $this->acquire('foo');
        $this->assertEquals(null, $this->lock);
        $this->release();
        $this->assertEquals(null, $this->lock);
    }

    /**
     * @return void
     */
    public function testAcquireWithErrorAndRelease(): void
    {
        $this->lockFactory = $this->getMockBuilder(LockFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lockMock = $this->getMockBuilder(LockInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->lockFactory->expects($this->atLeastOnce())
            ->method('createLock')
            ->with('foo')
            ->willReturn($lockMock);

        $lockMock->expects($this->atLeastOnce())
            ->method('acquire')
            ->with(true)
            ->willReturn(false);

        $this->acquire('foo');
        $this->assertEquals(null, $this->lock);

        $this->release();
        $this->assertEquals(null, $this->lock);
    }

    /**
     * @return void
     */
    public function testAcquireAndRelease(): void
    {
        $this->lockFactory = $this->getMockBuilder(LockFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lockMock = $this->getMockBuilder(LockInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->lockFactory->expects($this->atLeastOnce())
            ->method('createLock')
            ->with('foo')
            ->willReturn($lockMock);

        $lockMock->expects($this->atLeastOnce())
            ->method('acquire')
            ->with(true)
            ->willReturn(true);

        $this->acquire('foo');
        $this->assertEquals($lockMock, $this->lock);

        $this->release();
        $this->assertEquals(null, $this->lock);
    }
}
