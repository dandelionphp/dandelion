<?php


namespace Dandelion\Merger\Resources;


use ArrayObject;
use Dandelion\Filesystem\FilesystemInterface;

interface ResourceInterface
{
    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @return string
     */
    public function getFileName(): string;

    public function handle(ArrayObject $data);
}
