<?php

declare(strict_types=1);

namespace Dandelion\Filesystem;

use Codeception\Test\Unit;
use Exception;
use org\bovigo\vfs\vfsStream;
use function chdir;
use function getcwd;
use function rtrim;

class FilesystemTest extends Unit
{
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
        $root = vfsStream::setup('root');
        $url = vfsStream::newFile('file.ext')
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
        $root = vfsStream::setup('root');
        $url = $root->url() . DIRECTORY_SEPARATOR . 'file.ext';

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
        $root = vfsStream::setup('root');
        $url = vfsStream::newFile('file.ext')
            ->at($root)
            ->url();

        $this->assertEquals($this->filesystem, $this->filesystem->removeFile($url));
        $this->assertFileNotExists($url);
    }

    /**
     * @return void
     */
    public function testRemoveFileWithDirectoryAsParameter(): void
    {
        $root = vfsStream::setup('root');

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
        $root = vfsStream::setup('root', 0400);
        $url = vfsStream::newFile('file.ext', 0400)
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
        $root = vfsStream::setup('root');
        $url = vfsStream::newDirectory('dir')
            ->at($root)
            ->url();

        $this->assertEquals($this->filesystem, $this->filesystem->removeDirectory($url));
        $this->assertDirectoryNotExists($url);
    }

    /**
     * @return void
     */
    public function testRemoveDirectoryWithFileAsParameter(): void
    {
        $root = vfsStream::setup('root');
        $url = vfsStream::newFile('file.ext')
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
        $root = vfsStream::setup('root', 0555);
        $url = vfsStream::newDirectory('dir', 0555)
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
        $root = vfsStream::setup('root', null, [
            'dir' => [
                'subdir1' => [
                    'file1.ext' => 'Lorem ipsum ...',
                    'file2.ext' => 'Lorem ipsum ...'
                ], 'subdir2' => [
                    'file1.ext' => 'Lorem ipsum ...',
                ],
                'file1.ext' => 'Lorem ipsum ...'
            ]
        ]);
        $url = $root->getChild('dir')->url();

        $this->assertEquals($this->filesystem, $this->filesystem->removeDirectory($url));
        $this->assertDirectoryNotExists($url);
    }

    /**
     * @return void
     */
    public function testRemoveDirectoryWithContentAndWrongPermission(): void
    {
        $root = vfsStream::setup('root', 400, [
            'dir' => [
                'subdir1' => [
                    'file1.ext' => 'Lorem ipsum ...',
                    'file2.ext' => 'Lorem ipsum ...'
                ], 'subdir2' => [
                    'file1.ext' => 'Lorem ipsum ...',
                ],
                'file1.ext' => 'Lorem ipsum ...'
            ]
        ]);

        $root->getChild('dir')
            ->chmod(0400)
            ->chown(vfsStream::OWNER_ROOT)
            ->chgrp(vfsStream::GROUP_ROOT);

        $url = $root->getChild('dir')->url();

        try {
            $this->filesystem->removeDirectory($url);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }
}