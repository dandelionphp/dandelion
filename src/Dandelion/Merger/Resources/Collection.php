<?php


namespace Dandelion\Merger\Resources;


use Exception;
use Traversable;

class Collection implements CollectionInterface, \IteratorAggregate
{
    /**
     * @var \Dandelion\Merger\Resources\ResourceInterface[]
     */
    protected $resources = [];

    /**
     * Collection constructor.
     *
     * @param \Dandelion\Merger\Resources\ResourceInterface[] $resources
     */
    public function __construct(array $resources)
    {
        $this->init($resources);
    }

    /**
     * @param \Dandelion\Merger\Resources\ResourceInterface $resource
     *
     * @return \Dandelion\Merger\Resources\CollectionInterface
     */
    public function add(ResourceInterface $resource): CollectionInterface
    {
        $this->resources[$resource->getPath()] = $resource;
        return $this;
    }

    /**
     * @param string $name
     *
     * @return \Dandelion\Merger\Resources\ResourceInterface
     * @throws \Exception
     */
    public function get(string $name): ResourceInterface
    {
        if (array_key_exists($name, $this->resources)){
            return $this->resources[$name];
        }

        throw new Exception(sprintf('Resource with name "%s" not found!', $name));
    }

    /**
     * @param \Dandelion\Merger\Resources\ResourceInterface[] $resources
     *
     * @return void
     */
    protected function init(array $resources): void
    {
        foreach ($resources as $resource) {
            $this->add($resource);
        }
    }

    /**
     * @return \Traversable
     */
    public function getIterator(): Traversable
    {
        return yield from $this->resources;
    }


}
