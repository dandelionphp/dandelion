<?php

declare(strict_types=1);

namespace Dandelion\VersionControl\Platform;

use Dandelion\Configuration\Vcs;
use GuzzleHttp\ClientInterface as HttpClientInterface;

class GithubFactory implements PlatformFactoryInterface
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    protected $httpClient;

    /**
     * @param \GuzzleHttp\ClientInterface $httpClient
     */
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param \Dandelion\Configuration\Vcs $vcs
     *
     * @return \Dandelion\VersionControl\Platform\PlatformInterface
     */
    public function create(Vcs $vcs): PlatformInterface
    {
        return new Github($this->httpClient, $vcs);
    }
}
