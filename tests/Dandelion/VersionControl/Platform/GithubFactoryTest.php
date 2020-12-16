<?php

namespace Dandelion\VersionControl\Platform;

use Codeception\Test\Unit;
use Dandelion\Configuration\Vcs;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface as HttpClientInterface;

class GithubFactoryTest extends Unit
{
    /**
     * @var \GuzzleHttp\ClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $httpClientMock;

    /**
     * @var \Dandelion\Configuration\Vcs|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $vcsMock;
    /**
     * @var \Dandelion\VersionControl\Platform\GithubFactory
     */
    protected $githubFactory;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->httpClientMock = $this->getMockBuilder(HttpClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->vcsMock = $this->getMockBuilder(Vcs::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->githubFactory = new GithubFactory($this->httpClientMock);
    }

    /**
     * @return void
     */
    public function testCreate(): void
    {
        static::assertInstanceOf(Github::class, $this->githubFactory->create($this->vcsMock));
    }
}
