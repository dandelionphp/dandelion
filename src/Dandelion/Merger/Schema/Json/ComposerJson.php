<?php


namespace Dandelion\Merger\Schema\Json;


class ComposerJson
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $license;

    /**
     * @var array
     */
    protected $authors = [];

    /**
     * @var array
     */
    protected $require = [];

    /**
     * @var array
     */
    protected $requireDev = [];

    /**
     * @var array
     */
    protected $replace = [];

    /**
     * @var string
     */
    protected $minimumStability;

    /**
     * @var bool
     */
    protected $preferStable;

    /**
     * @var array
     */
    protected $autoload = [];

    /**
     * @var array
     */
    protected $autoloadDev = [];

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return ComposerJson
     */
    public function setName(string $name): ComposerJson
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return ComposerJson
     */
    public function setDescription(string $description): ComposerJson
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLicense(): ?string
    {
        return $this->license;
    }

    /**
     * @param string $license
     *
     * @return ComposerJson
     */
    public function setLicense(string $license): ComposerJson
    {
        $this->license = $license;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getAuthors(): ?array
    {
        return $this->authors;
    }

    /**
     * @param array $authors
     *
     * @return ComposerJson
     */
    public function setAuthors(array $authors): ComposerJson
    {
        $this->authors = $authors;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getRequire(): ?array
    {
        return $this->require;
    }

    /**
     * @param array $require
     *
     * @return ComposerJson
     */
    public function setRequire(array $require): ComposerJson
    {
        $this->require = $require;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getRequireDev(): ?array
    {
        return $this->requireDev;
    }

    /**
     * @param array $requireDev
     *
     * @return ComposerJson
     */
    public function setRequireDev(array $requireDev): ComposerJson
    {
        $this->requireDev = $requireDev;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getReplace(): ?array
    {
        return $this->replace;
    }

    /**
     * @param array $replace
     *
     * @return ComposerJson
     */
    public function setReplace(array $replace): ComposerJson
    {
        $this->replace = $replace;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMinimumStability(): ?string
    {
        return $this->minimumStability;
    }

    /**
     * @param string $minimumStability
     *
     * @return ComposerJson
     */
    public function setMinimumStability(string $minimumStability): ComposerJson
    {
        $this->minimumStability = $minimumStability;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isPreferStable(): ?bool
    {
        return $this->preferStable;
    }

    /**
     * @param bool $preferStable
     *
     * @return ComposerJson
     */
    public function setPreferStable(bool $preferStable): ComposerJson
    {
        $this->preferStable = $preferStable;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getAutoload(): ?array
    {
        return $this->autoload;
    }

    /**
     * @param array $autoload
     *
     * @return ComposerJson
     */
    public function setAutoload(array $autoload): ComposerJson
    {
        $this->autoload = $autoload;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getAutoloadDev(): ?array
    {
        return $this->autoloadDev;
    }

    /**
     * @param array $autoloadDev
     *
     * @return ComposerJson
     */
    public function setAutoloadDev(array $autoloadDev): ComposerJson
    {
        $this->autoloadDev = $autoloadDev;
        return $this;
    }
}
