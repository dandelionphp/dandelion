<?php

namespace Dandelion\Merger\Loader;

use Dandelion\Merger\Schema\Json\ComposerJson;
use SplFileInfo;

interface LoaderInterface
{
    /**
     * @param \SplFileInfo $fileInfo
     *
     * @return \Dandelion\Merger\Schema\Json\ComposerJson
     * @throws \Dandelion\Exception\IOException
     */
    public function load(SplFileInfo $fileInfo): ComposerJson;

    /**
     * @param \SplFileInfo $fileInfo
     *
     * @return string
     * @throws \Dandelion\Exception\IOException
     */
    public function loadRaw(SplFileInfo $fileInfo): string;
}
