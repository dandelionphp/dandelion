<?php


namespace Dandelion\Merger;


use ArrayObject;
use Dandelion\Filesystem\FilesystemInterface;
use Dandelion\Merger\Resources\CollectionInterface;
use Exception;
use Iterator;
use Symfony\Component\Finder\Finder;

class Merger implements MergerInterface
{
    /**
     * @var \Symfony\Component\Finder\Finder
     */
    protected $finder;

    /**
     * @var \Dandelion\Merger\Resources\CollectionInterface
     */
    protected $resourceCollection;

    /**
     * Merger constructor.
     *
     * @param \Symfony\Component\Finder\Finder $symfonyFinder
     * @param \Dandelion\Merger\Resources\CollectionInterface $resourceCollection
     */
    public function __construct(
        Finder $symfonyFinder,
        CollectionInterface $resourceCollection
    ) {
        $this->finder = $symfonyFinder;
        $this->resourceCollection = $resourceCollection;
    }

    public function merge(string $resourceName = null){
        $collection = $this->resourceCollection;

        if ($resourceName !== null){
            /** @var \Dandelion\Merger\Resources\ResourceInterface[] $collection */
            $collection = [$this->resourceCollection->get($resourceName)];
        }

        foreach ($collection as $resource){
            $this->finder->files()
                ->in($resource->getPath())
                ->name($resource->getFileName());

            if ($this->finder->count() === 0) {
                throw new Exception(sprintf('No "%s" files found.', $resource->getFileName()));
            }

            $resource->handle($this->createDataCollection($this->finder->getIterator()));
        }
    }

    /**
     * @param \Iterator $fileInfos
     *
     * @return \ArrayObject
     */
    protected function createDataCollection(Iterator $fileInfos): ArrayObject
    {
        $collection = new ArrayObject();
        foreach ($fileInfos as $fileInfo) {
            echo $fileInfo . PHP_EOL;
            $collection->append($fileInfo);
        }

        return $collection;
    }
}
