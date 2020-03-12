<?php

declare(strict_types=1);

namespace Dandelion\Console\Command;

use Dandelion\Operation\ReleaserInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function is_string;

class ReleaseAllCommand extends Command
{
    public const NAME = 'release:all';
    public const DESCRIPTION = 'Releases all packages.';

    /**
     * @var \Dandelion\Operation\ReleaserInterface
     */
    protected $releaser;

    /**
     * @param \Dandelion\Operation\ReleaserInterface $releaser
     */
    public function __construct(
        ReleaserInterface $releaser
    ) {
        parent::__construct();
        $this->releaser = $releaser;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName(static::NAME);
        $this->setDescription(static::DESCRIPTION);

        $this->addArgument('branch', InputArgument::REQUIRED, 'Branch');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $branch = $input->getArgument('branch');

        if (!is_string($branch)) {
            throw new InvalidArgumentException('Unsupported type for given argument');
        }

        $this->releaser->releaseAll($branch);

        return 0;
    }
}
