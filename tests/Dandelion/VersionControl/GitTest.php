<?php

declare(strict_types=1);

namespace Dandelion\VersionControl;

use Codeception\Test\Unit;
use Dandelion\Process\ProcessFactory;
use Exception;
use Symfony\Component\Process\Process;

class GitTest extends Unit
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Dandelion\Process\ProcessFactory
     */
    protected $processFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Process\Process
     */
    protected $processMock;

    /**
     * @var \Dandelion\VersionControl\GitInterface
     */
    protected $git;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->processFactoryMock = $this->getMockBuilder(ProcessFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->processMock = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->git = new Git($this->processFactoryMock);
    }

    /**
     * @return void
     */
    public function testClone(): void
    {
        $repository = 'https://github.com/daniel-rose/monorepo.git';

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with(['git', 'clone', $repository])
            ->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('run');

        $this->processMock->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(true);

        $this->processMock->expects($this->atLeastOnce())
            ->method('getOutput')
            ->willReturn('Output');

        $this->assertEquals($this->git, $this->git->clone($repository));
    }

    /**
     * @return void
     */
    public function testCloneWithTargetDirectory(): void
    {
        $repository = 'https://github.com/daniel-rose/monorepo.git';
        $targetDirectory = '.';

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with(['git', 'clone', $repository, $targetDirectory])
            ->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('run');

        $this->processMock->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(true);

        $this->processMock->expects($this->atLeastOnce())
            ->method('getOutput')
            ->willReturn('Output');

        $this->assertEquals($this->git, $this->git->clone($repository, $targetDirectory));
    }

    /**
     * @return void
     */
    public function testCloneWithError(): void
    {
        $repository = 'https://github.com/daniel-rose/monorepo.git';

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with(['git', 'clone', $repository])
            ->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('run');

        $this->processMock->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(false);

        $this->processMock->expects($this->never())
            ->method('getOutput');

        try {
            $this->git->clone($repository);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @return void
     */
    public function testCheckout(): void
    {
        $branch = 'master';

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with(['git', 'checkout', $branch])
            ->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('run');

        $this->processMock->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(true);

        $this->processMock->expects($this->atLeastOnce())
            ->method('getOutput')
            ->willReturn('Output');

        $this->assertEquals($this->git, $this->git->checkout($branch));
    }

    /**
     * @return void
     */
    public function testCheckoutWithError(): void
    {
        $branch = 'master';

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with(['git', 'checkout', $branch])
            ->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('run');

        $this->processMock->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(false);

        $this->processMock->expects($this->never())
            ->method('getOutput');

        try {
            $this->git->checkout($branch);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @return void
     */
    public function testTag(): void
    {
        $tagName = '1.0.0';

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with(['git', 'tag', $tagName])
            ->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('run');

        $this->processMock->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(true);

        $this->processMock->expects($this->atLeastOnce())
            ->method('getOutput')
            ->willReturn('Output');

        $this->assertEquals($this->git, $this->git->tag($tagName));
    }

    /**
     * @return void
     */
    public function testTagWithError(): void
    {
        $tagName = '1.0.0';

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with(['git', 'tag', $tagName])
            ->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('run');

        $this->processMock->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(false);

        $this->processMock->expects($this->never())
            ->method('getOutput');

        try {
            $this->git->tag($tagName);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @return void
     */
    public function testAddRemote(): void
    {
        $name = 'monorepo';
        $url = 'git@github.com:daniel-rose/monorepo.git';

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with(['git', 'remote', 'add', $name, $url])
            ->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('run');

        $this->processMock->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(true);

        $this->processMock->expects($this->atLeastOnce())
            ->method('getOutput')
            ->willReturn('Output');

        $this->assertEquals($this->git, $this->git->addRemote($name, $url));
    }

    /**
     * @return void
     */
    public function testAddRemoteWithError(): void
    {
        $name = 'monorepo';
        $url = 'git@github.com:daniel-rose/monorepo.git';

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with(['git', 'remote', 'add', $name, $url])
            ->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('run');

        $this->processMock->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(false);

        $this->processMock->expects($this->never())
            ->method('getOutput');

        try {
            $this->git->addRemote($name, $url);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @return void
     */
    public function testPull(): void
    {
        $remote = 'origin';
        $branch = 'master';

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with(['git', 'pull', $remote, $branch])
            ->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('run');

        $this->processMock->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(true);

        $this->processMock->expects($this->atLeastOnce())
            ->method('getOutput')
            ->willReturn('Output');

        $this->assertEquals($this->git, $this->git->pull($remote, $branch));
    }

    /**
     * @return void
     */
    public function testPullWithError(): void
    {
        $remote = 'origin';
        $branch = 'master';

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with(['git', 'pull', $remote, $branch])
            ->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('run');

        $this->processMock->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(false);

        $this->processMock->expects($this->never())
            ->method('getOutput');

        try {
            $this->git->pull($remote, $branch);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @return void
     */
    public function testPush(): void
    {
        $remote = 'test';
        $refSpec = sprintf('%s/:refs/heads/master', \sha1($remote));

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with(['git', 'push', $remote, $refSpec])
            ->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('run');

        $this->processMock->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(true);

        $this->processMock->expects($this->atLeastOnce())
            ->method('getOutput')
            ->willReturn('Output');

        $this->assertEquals($this->git, $this->git->push($remote, $refSpec));
    }

    /**
     * @return void
     */
    public function testPushWithError(): void
    {
        $remote = 'test';
        $refSpec = sprintf('%s/:refs/heads/master', \sha1($remote));

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with(['git', 'push', $remote, $refSpec])
            ->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('run');

        $this->processMock->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(false);

        $this->processMock->expects($this->never())
            ->method('getOutput');

        try {
            $this->git->push($remote, $refSpec);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @return void
     */
    public function testPushForcefully(): void
    {
        $remote = 'test';
        $refSpec = sprintf('%s/:refs/heads/master', \sha1($remote));

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with(['git', 'push', $remote, $refSpec, '--force'])
            ->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('run');

        $this->processMock->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(true);

        $this->processMock->expects($this->atLeastOnce())
            ->method('getOutput')
            ->willReturn('Output');

        $this->assertEquals($this->git, $this->git->pushForcefully($remote, $refSpec));
    }

    /**
     * @return void
     */
    public function testPushForcefullyWithError(): void
    {
        $remote = 'test';
        $refSpec = sprintf('%s/:refs/heads/master', \sha1($remote));

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with(['git', 'push', $remote, $refSpec, '--force'])
            ->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('run');

        $this->processMock->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(false);

        $this->processMock->expects($this->never())
            ->method('getOutput');

        try {
            $this->git->pushForcefully($remote, $refSpec);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @return void
     */
    public function testPushWithTags(): void
    {
        $remote = 'test';

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with(['git', 'push', $remote, '--tags'])
            ->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('run');

        $this->processMock->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(true);

        $this->processMock->expects($this->atLeastOnce())
            ->method('getOutput')
            ->willReturn('Output');

        $this->assertEquals($this->git, $this->git->pushWithTags($remote));
    }

    /**
     * @return void
     */
    public function testPushWithTagsAndError(): void
    {
        $remote = 'test';

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with(['git', 'push', $remote, '--tags'])
            ->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('run');

        $this->processMock->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(false);

        $this->processMock->expects($this->never())
            ->method('getOutput');

        try {
            $this->git->pushWithTags($remote);
        } catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @return void
     */
    public function testDescribeClosestTag(): void
    {
        $match = '1.0.0';

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with([
                'git',
                'describe',
                '--tags',
                '--abbrev=0',
                '--match',
                \sprintf('\'%s\'', $match)
            ])
            ->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('run');

        $this->processMock->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(true);

        $this->processMock->expects($this->atLeastOnce())
            ->method('getOutput')
            ->willReturn($match);

        $this->assertEquals($match, $this->git->describeClosestTag($match));
    }

    /**
     * @return void
     */
    public function testDescribeClosestTagWithError(): void
    {
        $match = '1.0.0';

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with([
                'git',
                'describe',
                '--tags',
                '--abbrev=0',
                '--match',
                \sprintf('\'%s\'', $match)
            ])
            ->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('run');

        $this->processMock->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(false);

        $this->processMock->expects($this->never())
            ->method('getOutput');

        $this->assertEquals(null, $this->git->describeClosestTag($match));
    }

    /**
     * @return void
     */
    public function testExistsRemote(): void
    {
        $remote = 'xxx';

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with([
                'git',
                'config',
                '--get',
                sprintf('remote.%s.url', $remote)
            ])
            ->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('run');

        $this->processMock->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(true);

        $this->processMock->expects($this->atLeastOnce())
            ->method('getOutput')
            ->willReturn('...');

        $this->assertTrue($this->git->existsRemote($remote));
    }

    /**
     * @return void
     */
    public function testExistsRemoteWithError(): void
    {
        $remote = 'xxx';

        $this->processFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with([
                'git',
                'config',
                '--get',
                sprintf('remote.%s.url', $remote)
            ])
            ->willReturn($this->processMock);

        $this->processMock->expects($this->atLeastOnce())
            ->method('run');

        $this->processMock->expects($this->atLeastOnce())
            ->method('isSuccessful')
            ->willReturn(false);

        $this->processMock->expects($this->never())
            ->method('getOutput');

        $this->assertFalse($this->git->existsRemote($remote));
    }
}
