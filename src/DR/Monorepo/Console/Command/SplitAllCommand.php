<?php

namespace DR\Monorepo\Console\Command;

use DR\Monorepo\Operation\SplitterInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function is_string;

class SplitAllCommand extends Command
{
    public const NAME = 'split';
    public const DESCRIPTION = 'Split all packages from mono to split.';

    /**
     * @var \DR\Monorepo\Operation\SplitterInterface
     */
    protected $splitter;

    /**
     * @param \DR\Monorepo\Operation\SplitterInterface $splitter
     */
    public function __construct(
        SplitterInterface $splitter
    ) {
        parent::__construct();
        $this->splitter = $splitter;
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
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $branch = $input->getArgument('branch');

        if (!is_string($branch)) {
            throw new InvalidArgumentException('Unsupported type for given argument');
        }

        $this->splitter->splitAll($branch);

        return null;
    }
}
