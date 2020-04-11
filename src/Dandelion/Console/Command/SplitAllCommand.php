<?php

declare(strict_types=1);

namespace Dandelion\Console\Command;

use Dandelion\Operation\AbstractOperation;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function is_string;

class SplitAllCommand extends Command
{
    public const NAME = 'split:all';
    public const DESCRIPTION = 'Splits all packages from mono to split.';

    /**
     * @var \Dandelion\Operation\AbstractOperation
     */
    protected $splitter;

    /**
     * @param \Dandelion\Operation\AbstractOperation $splitter
     */
    public function __construct(
        AbstractOperation $splitter
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
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $branch = $input->getArgument('branch');

        if (!is_string($branch)) {
            throw new InvalidArgumentException('Unsupported type for given argument');
        }

        $this->splitter->executeForAllRepositories($branch);

        return 0;
    }
}
