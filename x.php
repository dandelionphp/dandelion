<?php

use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

require_once './vendor/autoload.php';

class Config
{
    /**
     * @var \ArrayObject<string, Repo>
     */
    protected $repos;

    /**
     * @return \ArrayObject<string, Repo>
     */
    public function getRepos(): ArrayObject
    {
        return $this->repos;
    }

    /**
     * @param Repo[] $repos
     *
     * @return Config
     */
    public function setRepos(array $repos): Config
    {
        $this->repos = new ArrayObject($repos);
        return $this;
    }
}

class Repo
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $path;

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return Repo
     */
    public function setUrl(string $url): Repo
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return Repo
     */
    public function setPath(string $path): Repo
    {
        $this->path = $path;
        return $this;
    }
}

$json = '{"repos": {"x": {"url": "http://...", "path": "./x/"}}}';

$normalizer = [
    new ObjectNormalizer(null, null, null, new PhpDocExtractor()),
    new ArrayDenormalizer()
];

$serializer = new Serializer(
    $normalizer,
    [new JsonEncoder()]
);

/** @var Config $config */
$config = $serializer->deserialize($json, Config::class, 'json', []);

foreach ($config->getRepos() as $repoName => $repo) {
    echo sprintf('Name: %s, Url: %s, Path: %s', $repoName, $repo->getUrl(), $repo->getPath());
}

echo "\n" . $serializer->serialize($config, 'json');