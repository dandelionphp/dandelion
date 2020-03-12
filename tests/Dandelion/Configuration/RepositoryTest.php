<?php

declare(strict_types=1);

namespace Dandelion\Configuration;

use Codeception\Test\Unit;

class RepositoryTest extends Unit
{
    /**
     * @var \Dandelion\Configuration\Repository
     */
    protected $repository;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->repository = new Repository();
    }

    /**
     * @return void
     */
    public function testSetAndGetPath(): void
    {
        $path = '/path/to/x';

        $this->assertEquals($this->repository, $this->repository->setPath($path));
        $this->assertEquals($path, $this->repository->getPath());
    }

    /**
     * @return void
     */
    public function testSetAndGetUrl(): void
    {
        $url = 'https:///path/to/x';

        $this->assertEquals($this->repository, $this->repository->setUrl($url));
        $this->assertEquals($url, $this->repository->getUrl());
    }

    /**
     * @return void
     */
    public function testSetAndGetVersion(): void
    {
        $version = '1.0.0';

        $this->assertEquals($this->repository, $this->repository->setVersion($version));
        $this->assertEquals($version, $this->repository->getVersion());
    }
}
