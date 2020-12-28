<?php

declare(strict_types=1);

namespace Dandelion\VersionControl\Platform;

use Dandelion\Configuration\Repository;
use Dandelion\Configuration\Vcs;
use Dandelion\Exception\SplitRepositoryNotInitializedException;
use GuzzleHttp\ClientInterface as HttpClientInterface;

use function array_merge;
use function sprintf;
use function str_replace;
use function ucwords;

class Github implements PlatformInterface
{
    public const FORMAT_DESCRIPTION = '[READ ONLY] Subtree split of the %s module.';

    public const SPLIT_REPOSITORY_DEFAULTS = [
        'private' => false,
        'has_issues' => false,
        'has_projects' => false,
        'has_wiki' => false,
        'is_template' => false,
        'auto_init' => false,
        'allow_squash_merge' => true,
        'allow_merge_commit' => false,
        'allow_rebase_merge' => false,
    ];

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    protected $httpClient;

    /**
     * @var \Dandelion\Configuration\Vcs
     */
    protected $vcs;

    /**
     * @param \GuzzleHttp\ClientInterface $httpClient
     * @param \Dandelion\Configuration\Vcs $vcs
     */
    public function __construct(
        HttpClientInterface $httpClient,
        Vcs $vcs
    ) {
        $this->httpClient = $httpClient;
        $this->vcs = $vcs;
    }

    /**
     * @param \Dandelion\Configuration\Repository $repository
     *
     * @return string
     */
    public function getRepositoryUrl(Repository $repository): string
    {
        return sprintf(
            'https://%s@github.com/%s/%s.git',
            $this->vcs->getToken(),
            $this->vcs->getOwner()->getName(),
            $repository->getName()
        );
    }

    /**
     * @param \Dandelion\Configuration\Repository $repository
     * @return \Dandelion\VersionControl\Platform\PlatformInterface
     */
    public function initSplitRepository(Repository $repository): PlatformInterface
    {
        $owner = $this->vcs->getOwner();

        $url = sprintf('https://api.github.com/orgs/%s/repos', $owner->getName());

        if ($owner->getType() !== 'organisation') {
            $url = 'https://api.github.com/user/repos';
        }

        $response = $this->httpClient->request('POST', $url, [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => sprintf('token %s', $this->vcs->getToken())
            ], 'json' => array_merge(
                [
                    'name' => $repository->getName(),
                    'description' => sprintf(
                        static::FORMAT_DESCRIPTION,
                        str_replace('-', '', ucwords($repository->getName(), '-'))
                    )
                ],
                static::SPLIT_REPOSITORY_DEFAULTS
            ), 'http_errors' => false
        ]);

        if ($response->getStatusCode() !== 201) {
            throw new SplitRepositoryNotInitializedException(
                sprintf(
                    'Could not initialize split repository "%s/%s".',
                    $owner->getName(),
                    $repository->getName()
                )
            );
        }

        return $this;
    }

    /**
     * @param \Dandelion\Configuration\Repository $repository
     *
     * @return bool
     */
    public function existsSplitRepository(Repository $repository): bool
    {
        $url = sprintf(
            'https://api.github.com/repos/%s/%s',
            $this->vcs->getOwner()->getName(),
            $repository->getName()
        );

        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => sprintf('token %s', $this->vcs->getToken())
            ],
            'http_errors' => false
        ]);

        return $response->getStatusCode() === 200;
    }
}
