<?php

declare(strict_types=1);

namespace Dandelion\Filesystem;

use Codeception\Test\Unit;
use Exception;
use org\bovigo\vfs\vfsStream;

use function getcwd;
use function rtrim;

class FilesystemTest extends Unit
{
    protected const ROOT_DIR_NAME = 'root';
    protected const DIR_NAME = 'dir';
    protected const FILE_NAME = 'file.ext';

    /**
     * @var \Dandelion\Filesystem\FilesystemInterface
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
     * @return void
     */
    public function testReadFile(): void
    {
        $content = 'Lorem ipsum';
        $root = vfsStream::setup(static::ROOT_DIR_NAME);
        $url = vfsStream::newFile(static::FILE_NAME)
            ->at($root)
            ->setContent($content)
            ->url();

        $this->assertEquals($content, $this->filesystem->readFile($url));
    }

    /**
     * @return void
     */
    public function testReadFileWithError(): void
    {
        $root = vfsStream::setup(static::ROOT_DIR_NAME);
        $url = $root->url() . DIRECTORY_SEPARATOR . static::FILE_NAME;

        try {
            $this->filesystem->readFile($url);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @return void
     */
    public function testGetCurrentWorkingDirectory(): void
    {
        $this->assertEquals(
            rtrim(getcwd(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR,
            $this->filesystem->getCurrentWorkingDirectory()
        );
    }

    /**
     * @return void
     */
    public function testCreateDirectory(): void
    {
        $root = vfsStream::setup(static::ROOT_DIR_NAME);
        $pathToNewDirectory = $root->url() . DIRECTORY_SEPARATOR . static::DIR_NAME;

        $this->assertEquals($this->filesystem, $this->filesystem->createDirectory($pathToNewDirectory));
        $this->assertDirectoryExists($pathToNewDirectory);
    }

    /**
     * @return void
     */
    public function testCreateDirectoryWithError(): void
    {
        $root = vfsStream::setup(static::ROOT_DIR_NAME, 0400)
            ->chown(vfsStream::getCurrentUser() + 1)
            ->chgrp(vfsStream::getCurrentGroup() + 1);

        $pathToNewDirectory = $root->url() . DIRECTORY_SEPARATOR . static::DIR_NAME;

        try {
            $this->filesystem->createDirectory($pathToNewDirectory);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @return void
     */
    public function testChangeDirectory(): void
    {
        $currentWorkingDirectory = getcwd();
        $newWorkingDirectory = '/tmp';

        $this->assertEquals($this->filesystem, $this->filesystem->changeDirectory($newWorkingDirectory));
        $this->assertNotEquals($currentWorkingDirectory, getcwd());

        $this->assertEquals($this->filesystem, $this->filesystem->changeDirectory($currentWorkingDirectory));
        $this->assertEquals($currentWorkingDirectory, getcwd());
    }

    /**
     * @return void
     */
    public function testChangeDirectoryWithError(): void
    {
        $newWorkingDirectory = '/lorem/ipsum';

        try {
            $this->filesystem->changeDirectory($newWorkingDirectory);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testRemoveFile(): void
    {
        $root = vfsStream::setup(static::ROOT_DIR_NAME);
        $url = vfsStream::newFile(static::FILE_NAME)
            ->at($root)
            ->url();

        $this->assertEquals($this->filesystem, $this->filesystem->removeFile($url));
        $this->assertFileDoesNotExist($url);
    }

    /**
     * @return void
     */
    public function testRemoveFileWithDirectoryAsParameter(): void
    {
        $root = vfsStream::setup(static::ROOT_DIR_NAME);

        try {
            $this->filesystem->removeFile($root->url());
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @return void
     */
    public function testRemoveFileWithWrongPermission(): void
    {
        $root = vfsStream::setup(static::ROOT_DIR_NAME, 0400);
        $url = vfsStream::newFile(static::FILE_NAME, 0400)
            ->at($root)
            ->chown(vfsStream::OWNER_ROOT)
            ->chgrp(vfsStream::GROUP_ROOT)
            ->url();

        try {
            $this->filesystem->removeFile($url);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @return void
     */
    public function testRemoveDirectory(): void
    {
        $root = vfsStream::setup(static::ROOT_DIR_NAME);
        $url = vfsStream::newDirectory(static::DIR_NAME)
            ->at($root)
            ->url();

        $this->assertEquals($this->filesystem, $this->filesystem->removeDirectory($url));
        $this->assertDirectoryDoesNotExist($url);
    }

    /**
     * @return void
     */
    public function testRemoveDirectoryWithFileAsParameter(): void
    {
        $root = vfsStream::setup(static::ROOT_DIR_NAME);
        $url = vfsStream::newFile(static::FILE_NAME)
            ->at($root)
            ->url();

        try {
            $this->filesystem->removeDirectory($url);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @return void
     */
    public function testRemoveDirectoryWithWrongPermission(): void
    {
        $root = vfsStream::setup(static::ROOT_DIR_NAME, 0555);
        $url = vfsStream::newDirectory(static::DIR_NAME, 0555)
            ->chown(vfsStream::OWNER_ROOT)
            ->chgrp(vfsStream::GROUP_ROOT)
            ->at($root)
            ->url();

        try {
            $this->filesystem->removeDirectory($url);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @return void
     */
    public function testRemoveDirectoryWithContent(): void
    {
        $root = vfsStream::setup(static::ROOT_DIR_NAME, null, [
            static::DIR_NAME => [
                'subdir1' => [
                    'file1.ext' => 'Lorem ipsum ...',
                    'file2.ext' => 'Lorem ipsum ...'
                ], 'subdir2' => [
                    'file1.ext' => 'Lorem ipsum ...',
                ],
                'file1.ext' => 'Lorem ipsum ...'
            ]
        ]);
        $url = $root->getChild(static::DIR_NAME)->url();

        $this->assertEquals($this->filesystem, $this->filesystem->removeDirectory($url));
        $this->assertDirectoryDoesNotExist($url);
    }

    /**
     * @return void
     */
    public function testRemoveDirectoryWithContentAndWrongPermission(): void
    {
        $root = vfsStream::setup(static::ROOT_DIR_NAME, 400, [
            static::DIR_NAME => [
                'subdir1' => [
                    'file1.ext' => 'Lorem ipsum ...',
                    'file2.ext' => 'Lorem ipsum ...'
                ], 'subdir2' => [
                    'file1.ext' => 'Lorem ipsum ...',
                ],
                'file1.ext' => 'Lorem ipsum ...'
            ]
        ]);

        $root->getChild(static::DIR_NAME)
            ->chmod(0400)
            ->chown(vfsStream::OWNER_ROOT)
            ->chgrp(vfsStream::GROUP_ROOT);

        $url = $root->getChild(static::DIR_NAME)->url();

        try {
            $this->filesystem->removeDirectory($url);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }
}
