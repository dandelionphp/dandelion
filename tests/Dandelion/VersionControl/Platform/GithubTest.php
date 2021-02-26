<?php

namespace Dandelion\VersionControl\Platform;

use Codeception\Test\Unit;
use Dandelion\Configuration\Repository;
use Dandelion\Configuration\Vcs;
use Dandelion\Configuration\Vcs\Owner;
use Exception;
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
     * @var \Dandelion\Configuration\Vcs\Owner|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $ownerMock;

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

        $this->ownerMock = $this->getMockBuilder(Owner::class)
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
        $ownerName = 'foo';
        $expectedRepositoryUrl = sprintf('https://%s@github.com/%s/%s.git', $token, $ownerName, $repositoryName);

        $this->vcsMock->expects(static::atLeastOnce())
            ->method('getToken')
            ->willReturn('foo-token-123');

        $this->vcsMock->expects(static::atLeastOnce())
            ->method('getOwner')
            ->willReturn($this->ownerMock);

        $this->ownerMock->expects(static::atLeastOnce())
            ->method('getName')
            ->willReturn($ownerName);

        static::assertEquals(
            $expectedRepositoryUrl,
            $this->github->getRepositoryUrl($repositoryName)
        );
    }

    /**
     * @return void
     */
    public function testExistsSplitRepository(): void
    {
        $ownerName = 'foo';
        $repositoryName = 'bar';
        $token = 'foo-token-123';

        $url = sprintf(
            'https://api.github.com/repos/%s/%s',
            $ownerName,
            $repositoryName
        );

        $this->vcsMock->expects(static::atLeastOnce())
            ->method('getOwner')
            ->willReturn($this->ownerMock);

        $this->ownerMock->expects(static::atLeastOnce())
            ->method('getName')
            ->willReturn($ownerName);

        $this->vcsMock->expects(static::atLeastOnce())
            ->method('getToken')
            ->willReturn($token);

        $this->httpClientMock->expects(static::atLeastOnce())
            ->method('request')
            ->with('GET', $url, static::callback(
                static function (array $options) use ($token) {
                    return isset($options['headers']['Accept'], $options['headers']['Authorization'])
                        && $options['headers']['Accept'] === 'application/vnd.github.v3+json'
                        && $options['headers']['Authorization'] === sprintf('token %s', $token);
                }
            ))->willReturn($this->responseMock);

        $this->responseMock->expects(static::atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(200);

        static::assertTrue(
            $this->github->existsSplitRepository($repositoryName)
        );
    }

    /**
     * @return void
     */
    public function testInitSplitRepository(): void
    {
        $ownerName = 'foo';
        $ownerType = 'organisation';
        $repositoryName = 'bar';
        $token = 'foo-token-123';

        $url = sprintf(
            'https://api.github.com/orgs/%s/repos',
            $ownerName
        );

        $this->vcsMock->expects(static::atLeastOnce())
            ->method('getOwner')
            ->willReturn($this->ownerMock);

        $this->ownerMock->expects(static::atLeastOnce())
            ->method('getType')
            ->willReturn($ownerType);

        $this->ownerMock->expects(static::atLeastOnce())
            ->method('getName')
            ->willReturn($ownerName);

        $this->vcsMock->expects(static::atLeastOnce())
            ->method('getToken')
            ->willReturn($token);

        $this->httpClientMock->expects(static::atLeastOnce())
            ->method('request')
            ->with('POST', $url, static::callback(
                static function (array $options) use ($token, $repositoryName) {
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
            $this->github->initSplitRepository($repositoryName)
        );
    }

    /**
     * @return void
     */
    public function testInitSplitRepositoryWithErrors(): void
    {
        $ownerName = 'foo';
        $ownerType = 'organisation';
        $repositoryName = 'bar';
        $token = 'foo-token-123';

        $url = sprintf(
            'https://api.github.com/orgs/%s/repos',
            $ownerName
        );

        $this->vcsMock->expects(static::atLeastOnce())
            ->method('getOwner')
            ->willReturn($this->ownerMock);

        $this->ownerMock->expects(static::atLeastOnce())
            ->method('getType')
            ->willReturn($ownerType);

        $this->ownerMock->expects(static::atLeastOnce())
            ->method('getName')
            ->willReturn($ownerName);

        $this->vcsMock->expects(static::atLeastOnce())
            ->method('getToken')
            ->willReturn($token);

        $this->httpClientMock->expects(static::atLeastOnce())
            ->method('request')
            ->with('POST', $url, static::callback(
                static function (array $options) use ($token, $repositoryName) {
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
            $this->github->initSplitRepository($repositoryName);
            self::fail();
        } catch (Exception $exception) {
        }
    }

    /**
     * @return void
     */
    public function testInitSplitRepositoryForAuthenticatedUser(): void
    {
        $ownerName = 'foo';
        $ownerType = 'authenticated-user';
        $repositoryName = 'bar';
        $token = 'foo-token-123';

        $url = sprintf('https://api.github.com/user/repos');

        $this->vcsMock->expects(static::atLeastOnce())
            ->method('getOwner')
            ->willReturn($this->ownerMock);

        $this->ownerMock->expects(static::atLeastOnce())
            ->method('getType')
            ->willReturn($ownerType);

        $this->ownerMock->expects(static::atLeastOnce())
            ->method('getName')
            ->willReturn($ownerName);

        $this->vcsMock->expects(static::atLeastOnce())
            ->method('getToken')
            ->willReturn($token);

        $this->httpClientMock->expects(static::atLeastOnce())
            ->method('request')
            ->with('POST', $url, static::callback(
                static function (array $options) use ($token, $repositoryName) {
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
            $this->github->initSplitRepository($repositoryName);
            self::fail();
        } catch (Exception $exception) {
        }
    }
}
