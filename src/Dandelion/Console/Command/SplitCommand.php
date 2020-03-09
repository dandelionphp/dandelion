<?php

declare(strict_types=1);

namespace Dandelion\Console\Command;

use Dandelion\Operation\SplitterInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function is_string;

class SplitCommand extends Command
{
    public const NAME = 'split';
    public const DESCRIPTION = 'Splits package from mono to split.';

    /**
     * @var \Dandelion\Operation\SplitterInterface
     */
    protected $splitter;

    /**
     * @param \Dandelion\Operation\SplitterInterface $splitter
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

        $this->addArgument('repositoryName', InputArgument::REQUIRED, 'Name of split repository');
        $this->addArgument('branch', InputArgument::REQUIRED, 'Branch');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repositoryName = $input->getArgument('repositoryName');
        $branch = $input->getArgument('branch');

        if (!is_string($repositoryName) || !is_string($branch)) {
            throw new InvalidArgumentException('Unsupported type for given arguments');
        }

        $this->splitter->split($repositoryName, $branch);

        return 0;
    }
}
