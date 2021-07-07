<?php


namespace Dandelion\Merger\Resources;


use ArrayObject;
use Dandelion\Filesystem\FilesystemInterface;
use Dandelion\Merger\Loader\LoaderInterface;
use Dandelion\Merger\Schema\Json\ComposerJson;

class ComposerJsonResource implements ResourceInterface
{
    public const PATH_PATTERN = '%s/resources/bundles/*';
    public const FILE_NAME = 'composer.json';
    public const PREFER_STABLE = true;
    public const MINIMUM_STABILITY = 'dev';
    public const NAME = 'fond-of-oryx/fond-of-oryx';
    public const DESCRIPTION = 'FOND OF Oryx Monorepo';
    public const LICENSE = 'MIT';

    /**
     * @var \Dandelion\Filesystem\FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var \Dandelion\Merger\Loader\LoaderInterface
     */
    protected $loader;

    /**
     * @var \Dandelion\Merger\Schema\Json\ComposerJson
     */
    protected $mainComposerJson;

    /**
     * ComposerJsonResource constructor.
     *
     * @param \Dandelion\Filesystem\FilesystemInterface $filesystem
     */
    public function __construct(FilesystemInterface $filesystem, LoaderInterface $loader)
    {
        $this->filesystem = $filesystem;
        $this->loader = $loader;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return sprintf(static::PATH_PATTERN, rtrim($this->filesystem->getCurrentWorkingDirectory(), '/'));
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return static::FILE_NAME;
    }

    /**
     * @param \ArrayObject|\SplFileInfo[] $data
     *
     * @throws \Dandelion\Exception\IOException
     */
    public function handle(ArrayObject $data)
    {
        $this->mainComposerJson = new ComposerJson();
        $this->mainComposerJson
            ->setName(static::NAME)
            ->setDescription(static::DESCRIPTION)
            ->setLicense(static::LICENSE)
            ->setMinimumStability(static::MINIMUM_STABILITY)
            ->setPreferStable(static::PREFER_STABLE);

        foreach ($data as $fileInfo) {
            $path = $fileInfo->getPath();
            $composerJson = $this->loader->load($fileInfo);
            $composerJson->setAutoload($this->createPsr4Autoload($path . '/src'));
            $composerJson->setAutoloadDev($this->createPsr4Autoload($path . '/tests'));
            $this->mergeToMain($composerJson);
        }

        var_dump($this->mainComposerJson);
    }

    protected function mergeToMain(ComposerJson $composerJson): void
    {
        $this->mainComposerJson->setAutoload(array_merge_recursive($this->mainComposerJson->getAutoload(), $composerJson->getAutoload()));
        $this->mainComposerJson->setAutoloadDev(array_merge_recursive($this->mainComposerJson->getAutoloadDev(), $composerJson->getAutoloadDev()));
        $this->mainComposerJson->setAuthors($this->mergeAuthors($composerJson->getAuthors()));
        $this->mainComposerJson->setReplace($this->mergeReplace($composerJson));
        $this->mainComposerJson->setRequire($this->mergeRequire($composerJson));
        $this->mainComposerJson->setRequireDev($this->mergeRequireDev($composerJson));
    }

    /**
     * @param array $authors
     *
     * @return array
     */
    protected function mergeAuthors(array $authors): array
    {
        $mainAuthors = $this->mainComposerJson->getAuthors();
        if (empty($mainAuthors)) {
            return $authors;
        }

        foreach ($authors as $author) {
            $in = false;
            foreach ($mainAuthors as $mainAuthor) {
                if ($author['email'] === $mainAuthor['email']) {
                    $in = true;
                    break;
                }
            }
            if ($in === false) {
                $mainAuthors[] = $author;
            }
        }

        return $mainAuthors;
    }

    /**
     * @param \Dandelion\Merger\Schema\Json\ComposerJson $composerJson
     *
     * @return array
     */
    protected function mergeReplace(ComposerJson $composerJson): array
    {
        $replaces = $this->mainComposerJson->getReplace();
        if (empty($replaces)) {
            $replaces = [];
        }

        if (empty($composerJson->getReplace()) === false) {
            $replaces = array_merge_recursive($replaces, $composerJson->getReplace());
        }

        $replaces[$composerJson->getName()] = '*';

        return $replaces;
    }

    /**
     * @param string $path
     *
     * @return array|null
     */
    protected function getDirectoriesInPath(string $path): ?array
    {
        if (is_dir($path) === false) {
            var_dump($path);
            return null;
        }

        foreach (array_diff(scandir($path, 1), ['..', '.', '_data', '_output', '_support']) as $found) {
            if (is_dir(sprintf('%s/%s', $path, $found))) {
                $directories[] = $found;
            }
        }

        return $directories;
    }

    protected function mergeRequire(ComposerJson $composerJson)
    {
        $requireMain = $this->getRequire($this->mainComposerJson);
        $require = $this->getRequire($composerJson);

        return $this->handleVersionMerge($require, $requireMain);
    }

    protected function mergeRequireDev(ComposerJson $composerJson)
    {
        $requireMain = $this->getRequireDev($this->mainComposerJson);
        $require = $this->getRequireDev($composerJson);

        return $this->handleVersionMerge($require, $requireMain);
    }

    /**
     * @param string $path
     *
     * @return array
     */
    protected function createPsr4Autoload(string $path): array
    {
        $autoload = [];
        $vendor = '';
        $levels = [];
        $resourcePath = sprintf('%s/resources/', rtrim($this->filesystem->getCurrentWorkingDirectory(), '/'));
        $cleanedPath = str_replace($resourcePath, '', $path);

        for ($i = 1; $i <= 3; $i++) {
            switch ($i) {
                case 1:
                    $directories = $this->getDirectoriesInPath($path);
                    if (is_array($directories) && count($directories) > 0) {
                        $vendor = current($directories);
                    }
                    break;
                case 2:
                    $levels = $this->getDirectoriesInPath(sprintf('%s/%s', $path, $vendor));
                    break;
                case 3:
                    if (is_array($levels) && count($levels) > 0) {
                        foreach ($levels as $level) {
                            $directories = $this->getDirectoriesInPath(sprintf('%s/%s/%s', $path, $vendor, $level));
                            if (is_array($directories) && count($directories) > 0) {
                                $packageName = current($directories);
                                $namespace = sprintf('%s\\\%s\\\%s\\\\', $vendor, $level, $packageName);
                                $packagePath = sprintf('%s/%s/%s/%s', $cleanedPath, $vendor, $level, $packageName);
                                $autoload[$namespace] = $packagePath;
                            }
                        }
                    }
                    break;
            }
        }
        return ['psr-4' => $autoload];
    }

    /**
     * @param string $version1
     * @param string $version2
     *
     * @return string
     */
    protected function mergeVersions(string $version1, string $version2): string
    {
        //ToDo make real merge
        $versions1 = explode(' || ', $version1);
        $versions2 = explode(' || ', $version2);
        if (count($versions1) > count($versions2)) {
            return implode(' || ', $versions1);
        }
        return implode(' || ', $versions2);
    }

    /**
     * @param \Dandelion\Merger\Schema\Json\ComposerJson $composerJson
     *
     * @return array
     */
    protected function getRequire(ComposerJson $composerJson): array
    {
        $require = $composerJson->getRequire();
        if (empty($require)) {
            $require = [];
        }
        return $require;
    }

    /**
     * @param \Dandelion\Merger\Schema\Json\ComposerJson $composerJson
     *
     * @return array
     */
    protected function getRequireDev(ComposerJson $composerJson): array
    {
        $require = $composerJson->getRequireDev();
        if (empty($require)) {
            $require = [];
        }
        return $require;
    }

    /**
     * @param array $require
     * @param array $requireMain
     *
     * @return array
     */
    protected function handleVersionMerge(array $require, array $requireMain): array
    {
        foreach ($require as $name => $version) {
            if (array_key_exists($name, $requireMain) === false) {
                $requireMain[$name] = $version;
                continue;
            }
            $requireMain[$name] = $this->mergeVersions($version, $requireMain[$name]);
        }
        return $requireMain;
}
}
