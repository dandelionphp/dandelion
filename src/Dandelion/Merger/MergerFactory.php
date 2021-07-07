<?php


namespace Dandelion\Merger;


use Dandelion\Filesystem\FilesystemInterface;
use Dandelion\Merger\Loader\ComposerJsonLoader;
use Dandelion\Merger\Loader\LoaderInterface;
use Symfony\Component\Serializer\SerializerInterface;

class MergerFactory
{
    /**
     * @var \Dandelion\Filesystem\FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var \Symfony\Component\Serializer\SerializerInterface
     */
    protected $serializer;

    /**
     * MergerFactory constructor.
     *
     * @param \Dandelion\Filesystem\FilesystemInterface $filesystem
     * @param \Symfony\Component\Serializer\SerializerInterface $serializer
     */
    public function __construct(FilesystemInterface $filesystem, SerializerInterface $serializer)
    {
        $this->filesystem = $filesystem;
        $this->serializer = $serializer;
    }

    /**
     * @return \Dandelion\Merger\Loader\LoaderInterface
     */
    public function createComposerJsonLoader(): LoaderInterface
    {
        return new ComposerJsonLoader($this->filesystem, $this->serializer);
    }
}
