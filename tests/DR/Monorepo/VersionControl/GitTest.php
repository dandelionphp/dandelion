<?php

namespace DR\Monorepo\VersionControl;

use Codeception\Test\Unit;
use DR\Monorepo\Process\ProcessFactory;
use Exception;
use Symfony\Component\Process\Process;

class GitTest extends Unit
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\DR\Monorepo\Process\ProcessFactory
     */
    protected $processFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Process\Process
     */
    protected $processMock;

    /**
     * @var \DR\Monorepo\VersionControl\Git
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

        $this->assertEquals($this->git, $this->git->clone($repository));
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

        try {
            $this->git->clone($repository);
            $this->fail();
        } catch (Exception $e) {}

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

        try {
            $this->git->checkout($branch);
            $this->fail();
        } catch (Exception $e) {}
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

        try {
            $this->git->tag($tagName);
            $this->fail();
        } catch (Exception $e) {}
    }
}