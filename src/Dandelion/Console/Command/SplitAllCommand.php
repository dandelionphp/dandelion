<?php

declare(strict_types=1);

namespace Dandelion\Console\Command;

use Dandelion\Operation\AbstractOperation;
use Dandelion\Operation\Result\MessageInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function is_string;
use function sprintf;

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

        $output->writeln('Splitting monorepo packages:');
        $output->writeln('---------------------------------');

        $result = $this->splitter->executeForAllRepositories($branch);

        foreach ($result->getMessages() as $message) {
            $symbol = $message->getType() === MessageInterface::TYPE_INFO ? '<fg=green>✔</>' : '<fg=red>✗</>';
            $output->writeln(sprintf('%s %s', $symbol, $message->getText()));
        }

        $output->writeln('---------------------------------');
        $output->writeln('Finished');

        return 0;
    }
}
