<?php

namespace Dandelion\Lock;

trait LockTrait
{
    /**
     * @var \Symfony\Component\Lock\LockFactory
     */
    private $lockFactory;

    /**
     * @var \Symfony\Component\Lock\Lock
     */
    private $lock;

    /**
     * @param string $identifier
     *
     * @return bool
     */
    private function acquire(string $identifier): bool
    {
        if ($this->lockFactory === null) {
            return true;
        }

        $this->lock = $this->lockFactory->createLock($identifier);

        if (!$this->lock->acquire(true)) {
            $this->lock = null;
            return false;
        }

        return true;
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
