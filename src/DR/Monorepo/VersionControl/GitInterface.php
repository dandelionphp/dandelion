<?php


namespace DR\Monorepo\VersionControl;


interface GitInterface
{
    /**
     * @param string $repository
     *
     * @return \DR\Monorepo\VersionControl\GitInterface
     */
    public function clone(string $repository): GitInterface;

    /**
     * @param string $branch
     *
     * @return \DR\Monorepo\VersionControl\GitInterface
     */
    public function checkout(string $branch): GitInterface;

    /**
     * @param string $tagName
     *
     * @return \DR\Monorepo\VersionControl\GitInterface
     */
    public function tag(string $tagName): GitInterface;

    /**
     * @param string $name
     * @param string $url
     *
     * @return \DR\Monorepo\VersionControl\GitInterface
     */
    public function addRemote(string $name, string $url): GitInterface;

    /**
     * @param string $remote
     * @param string|null $localBranch
     *
     * @return \DR\Monorepo\VersionControl\GitInterface
     */
    public function pull(string $remote, ?string $localBranch = null): GitInterface;

    /**
     * @param string $remote
     * @param string $refSpec
     * @param bool $includeTags
     *
     * @param bool $force
     * @return \DR\Monorepo\VersionControl\GitInterface
     */
    public function push(
        string $remote,
        ?string $refSpec = null,
        bool $includeTags = false,
        bool $force = false
    ): GitInterface;
}