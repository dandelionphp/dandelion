<?php

namespace Dandelion\VersionControl\Platform;

use Codeception\Test\Unit;
use Dandelion\Configuration\Repository;
use Dandelion\Configuration\Vcs;
use GuzzleHttp\ClientInterface as HttpClientInterface;
use Psr\Http\Message\ResponseInterface;

class GithubTest extends Unit
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
     * @var \Dandelion\Configuration\Repository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $repositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Http\Message\ResponseInterface
     */
    protected $responseMock;

    /**
     * @var \Dandelion\VersionControl\Platform\Github
     */
    protected $github;

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

        $this->repositoryMock = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->github = new Github(
            $this->httpClientMock,
            $this->vcsMock
        );
    }

    /**
     * @return void
     */
    public function testGetRepositoryUrl(): void
    {
        $token = 'foo-token-123';
        $repositoryName = 'bar';
        $owner = 'foo';
        $expectedRepositoryUrl = sprintf('https://%s@github.com/%s/%s.git', $token, $owner, $repositoryName);

        $this->vcsMock->expects(static::atLeastOnce())
            ->method('getToken')
            ->willReturn('foo-token-123');

        $this->vcsMock->expects(static::atLeastOnce())
            ->method('getOwner')
            ->willReturn('foo');

        $this->repositoryMock->expects(static::atLeastOnce())
            ->method('getName')
            ->willReturn('bar');

        static::assertEquals(
            $expectedRepositoryUrl,
            $this->github->getRepositoryUrl($this->repositoryMock)
        );
    }

    /**
     * @return void
     */
    public function testExistsSplitRepository(): void
    {
        $owner = 'foo';
        $repositoryName = 'bar';
        $token = 'foo-token-123';

        $url = sprintf(
            'https://api.github.com/repos/%s/%s',
            $owner,
            $repositoryName
        );

        $this->vcsMock->expects(static::atLeastOnce())
            ->method('getOwner')
            ->willReturn($owner);

        $this->repositoryMock->expects(static::atLeastOnce())
            ->method('getName')
            ->willReturn($repositoryName);

        $this->vcsMock->expects(static::atLeastOnce())
            ->method('getToken')
            ->willReturn($token);

        $this->httpClientMock->expects(static::atLeastOnce())
            ->method('request')
            ->with('GET', $url, static::callback(
                static function(array $options) use ($token) {
                    return isset($options['headers']['Accept'], $options['headers']['Authorization'])
                        && $options['headers']['Accept'] === 'application/vnd.github.v3+json'
                        && $options['headers']['Authorization'] === sprintf('token %s', $token);
                }
            ))->willReturn($this->responseMock);

        $this->responseMock->expects(static::atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(200);

        static::assertTrue(
            $this->github->existsSplitRepository($this->repositoryMock)
        );
    }

    /**
     * @return void
     */
    public function testInitSplitRepository(): void
    {
        $owner = 'foo';
        $repositoryName = 'bar';
        $token = 'foo-token-123';

        $url = sprintf(
            'https://api.github.com/orgs/%s/repos',
            $owner
        );

        $this->vcsMock->expects(static::atLeastOnce())
            ->method('getOwner')
            ->willReturn($owner);

        $this->repositoryMock->expects(static::atLeastOnce())
            ->method('getName')
            ->willReturn($repositoryName);

        $this->vcsMock->expects(static::atLeastOnce())
            ->method('getToken')
            ->willReturn($token);

        $this->httpClientMock->expects(static::atLeastOnce())
            ->method('request')
            ->with('POST', $url, static::callback(
                static function(array $options) use ($token, $repositoryName) {
                    return isset(
                            $options['headers']['Accept'],
                            $options['headers']['Authorization'],
                            $options['json']['name'],
                            $options['json']['description']
                        )
                        && $options['headers']['Accept'] === 'application/vnd.github.v3+json'
                        && $options['headers']['Authorization'] === sprintf('token %s', $token)
                        && $options['json']['name'] === $repositoryName
                        && $options['json']['description'] === sprintf(
                            Github::FORMAT_DESCRIPTION,
                            ucwords($repositoryName, '-')
                        );
                }
            ))->willReturn($this->responseMock);

        $this->responseMock->expects(static::atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(201);

        static::assertEquals(
            $this->github,
            $this->github->initSplitRepository($this->repositoryMock)
        );
    }

    /**
     * @return void
     */
    public function testInitSplitRepositoryWithErrors(): void
    {
        $owner = 'foo';
        $repositoryName = 'bar';
        $token = 'foo-token-123';

        $url = sprintf(
            'https://api.github.com/orgs/%s/repos',
            $owner
        );

        $this->vcsMock->expects(static::atLeastOnce())
            ->method('getOwner')
            ->willReturn($owner);

        $this->repositoryMock->expects(static::atLeastOnce())
            ->method('getName')
            ->willReturn($repositoryName);

        $this->vcsMock->expects(static::atLeastOnce())
            ->method('getToken')
            ->willReturn($token);

        $this->httpClientMock->expects(static::atLeastOnce())
            ->method('request')
            ->with('POST', $url, static::callback(
                static function(array $options) use ($token, $repositoryName) {
                    return isset(
                            $options['headers']['Accept'],
                            $options['headers']['Authorization'],
                            $options['json']['name'],
                            $options['json']['description']
                        )
                        && $options['headers']['Accept'] === 'application/vnd.github.v3+json'
                        && $options['headers']['Authorization'] === sprintf('token %s', $token)
                        && $options['json']['name'] === $repositoryName
                        && $options['json']['description'] === sprintf(
                            Github::FORMAT_DESCRIPTION,
                            ucwords($repositoryName, '-')
                        );
                }
            ))->willReturn($this->responseMock);

        $this->responseMock->expects(static::atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(500);

        try {
            $this->github->initSplitRepository($this->repositoryMock);
            self::fail();
        } catch (\Exception $exception) {
        }
    }
}
