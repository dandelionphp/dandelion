<?php

declare(strict_types=1);

namespace Dandelion\Lock;

trait LockTrait
{
    /**
     * @var \Symfony\Component\Lock\LockFactory
     */
    private $lockFactory;

    /**
     * @var \Symfony\Component\Lock\LockInterface|null
     */
    private $lock;

    /**
     * @param string $identifier
     *
     * @return void
     */
    private function acquire(string $identifier): void
    {
        if ($this->lockFactory === null) {
            return;
        }

        $this->lock = $this->lockFactory->createLock($identifier);

        if (!$this->lock->acquire(true)) {
            $this->lock = null;
        }
    }

    /**
     * @return void
     */
    private function release(): void
    {
        if ($this->lock === null) {
            return;
        }

        $this->lock->release();
        $this->lock = null;
    }
}
