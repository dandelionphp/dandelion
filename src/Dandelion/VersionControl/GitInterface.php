<?php

declare(strict_types=1);

namespace Dandelion\VersionControl;

interface GitInterface
{
    /**
     * @param string $repository
     * @param string|null $directory
     *
     * @return \Dandelion\VersionControl\GitInterface
     */
    public function clone(string $repository, string $directory = null): GitInterface;

    /**
     * @param string $branch
     *
     * @return \Dandelion\VersionControl\GitInterface
     */
    public function checkout(string $branch): GitInterface;

    /**
     * @param string $tagName
     *
     * @return \Dandelion\VersionControl\GitInterface
     */
    public function tag(string $tagName): GitInterface;

    /**
     * @param string $name
     * @param string $url
     *
     * @return \Dandelion\VersionControl\GitInterface
     */
    public function addRemote(string $name, string $url): GitInterface;

    /**
     * @param string $remote
     * @param string|null $localBranch
     *
     * @return \Dandelion\VersionControl\GitInterface
     */
    public function pull(string $remote, ?string $localBranch = null): GitInterface;

    /**
     * @param string $remote
     * @param string|null $refSpec
     *
     * @return \Dandelion\VersionControl\GitInterface
     */
    public function push(string $remote, ?string $refSpec = null): GitInterface;

    /**
     * @param string $remote
     * @param string|null $refSpec
     *
     * @return \Dandelion\VersionControl\GitInterface
     */
    public function pushForcefully(string $remote, ?string $refSpec = null): GitInterface;

    /**
     * @param string $remote
     *
     * @return \Dandelion\VersionControl\GitInterface
     */
    public function pushWithTags(string $remote): GitInterface;
}
