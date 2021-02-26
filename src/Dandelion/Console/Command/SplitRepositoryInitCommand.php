<?php

declare(strict_types=1);

namespace Dandelion\Console\Command;

use Dandelion\Operation\SplitRepositoryInitializerInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SplitRepositoryInitCommand extends Command
{
    public const NAME = 'split-repository:init';
    public const DESCRIPTION = 'Init split repository on vcs platform.';

    /**
     * @var \Dandelion\Operation\SplitRepositoryInitializerInterface
     */
    protected $initializer;

    /**
     * @param \Dandelion\Operation\SplitRepositoryInitializerInterface $initializer
     */
    public function __construct(
        SplitRepositoryInitializerInterface $initializer
    ) {
        parent::__construct();
        $this->initializer = $initializer;
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
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repositoryName = $input->getArgument('repositoryName');

        if (!is_string($repositoryName)) {
            throw new InvalidArgumentException('Unsupported type for given argument');
        }

        $this->initializer->executeForSingleRepository($repositoryName);

        return 0;
    }
}
