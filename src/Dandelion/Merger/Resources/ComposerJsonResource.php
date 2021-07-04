<?php


namespace Dandelion\Merger\Resources;


use ArrayObject;
use Dandelion\Filesystem\FilesystemInterface;

class ComposerJsonResource implements ResourceInterface
{
    public const PATH_PATTERN = '%s/resources/bundles/*';
    public const FILE_NAME = 'composer.json';

    /**
     * @var \Dandelion\Filesystem\FilesystemInterface
     */
    protected $filesystem;

    /**
     * ComposerJsonResource constructor.
     *
     * @param \Dandelion\Filesystem\FilesystemInterface $filesystem
     */
    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
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

    public function handle(ArrayObject $data){

    }
}
