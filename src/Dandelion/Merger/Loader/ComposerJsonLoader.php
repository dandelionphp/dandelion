<?php


namespace Dandelion\Merger\Loader;


use Dandelion\Exception\InvalidTypeException;
use Dandelion\Exception\IOException;
use Dandelion\Filesystem\FilesystemInterface;
use Dandelion\Merger\Schema\Json\ComposerJson;
use SplFileInfo;
use Symfony\Component\Serializer\SerializerInterface;

class ComposerJsonLoader implements LoaderInterface
{
    public const REPLACE = [
        'require-dev' => 'requireDev',
        'minimum-stability' => 'minimumStability',
        'prefer-stable' => 'preferStable',
        'autoload-dev' => 'autoloadDev',
    ];

    /**
     * @var \Dandelion\Filesystem\FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var \Symfony\Component\Serializer\SerializerInterface
     */
    protected $serializer;

    /**
     * ComposerJsonLoader constructor.
     *
     * @param \Dandelion\Filesystem\FilesystemInterface $filesystem
     * @param \Symfony\Component\Serializer\SerializerInterface $serializer
     */
    public function __construct(
        FilesystemInterface $filesystem,
        SerializerInterface $serializer
    ) {
        $this->filesystem = $filesystem;
        $this->serializer = $serializer;
    }

    /**
     * @param \SplFileInfo $fileInfo
     *
     * @return \Dandelion\Merger\Schema\Json\ComposerJson
     * @throws \Dandelion\Exception\IOException
     */
    public function load(SplFileInfo $fileInfo): ComposerJson
    {
        $composerData = json_decode($this->loadRaw($fileInfo), true);
        $newComposerData = [];
        foreach ($composerData as $key => $value){
            if (array_key_exists($key, static::REPLACE)){
                $key = static::REPLACE[$key];
            }
            $newComposerData[$key] = $value;
        }

        $composerJson = $this->serializer->deserialize(
            json_encode($newComposerData),
            ComposerJson::class,
            'json'
        );

        if (!is_object($composerJson) || !($composerJson instanceof ComposerJson)) {
            throw new InvalidTypeException('Invalid type of deserialized data.');
        }

        return $composerJson;
    }

    /**
     * @param \SplFileInfo $fileInfo
     *
     * @return string
     * @throws \Dandelion\Exception\IOException
     */
    public function loadRaw(SplFileInfo $fileInfo): string
    {
        $realPathToComposerJsonFile = $fileInfo->getRealPath();

        if ($realPathToComposerJsonFile === false) {
            throw new IOException('Json file does not exists.');
        }

        return $this->filesystem->readFile($realPathToComposerJsonFile);
    }
}
