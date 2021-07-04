<?php


namespace Dandelion\Merger\Resources;


interface CollectionInterface
{
    public function add(ResourceInterface $resource): CollectionInterface;

    /**
     * @param string $name
     *
     * @return \Dandelion\Merger\Resources\ResourceInterface
     * @throws \Exception
     */
    public function get(string $name): ResourceInterface;
}
