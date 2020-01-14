<?php

namespace DR\Monorepo\Filesystem;

use Codeception\Test\Unit;
use DR\Monorepo\Exception\IOException;
use org\bovigo\vfs\vfsStream;
use function file_put_contents;

class FilesystemTest extends Unit
{
    /**
     * @var \DR\Monorepo\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->filesystem = new Filesystem();
    }

    /**
     * @throws \DR\Monorepo\Exception\IOException
     *
     * @return void
     */
    public function testReadFile(): void
    {
        $root = vfsStream::setup('root', null, ['to' => []]);
        $pathToNonExistingFile = $root->url() . '/to/file.ext';
        $content = 'Lorem ipsum';
        @file_put_contents($pathToNonExistingFile, $content);

        $this->assertEquals($content, $this->filesystem->readFile($pathToNonExistingFile));
    }

    /**
     * @throws \DR\Monorepo\Exception\IOException
     *
     * @return void
     */
    public function testReadFileWithError(): void
    {
        $root = vfsStream::setup('root', null, ['to' => []]);
        $pathToNonExistingFile = $root->url() . '/to/file.ext';

        try {
            $this->filesystem->readFile($pathToNonExistingFile);
            $this->fail();
        } catch (IOException $e) {
        }
    }
}