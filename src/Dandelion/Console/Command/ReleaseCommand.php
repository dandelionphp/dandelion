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

class ReleaseCommand extends Command
{
    public const NAME = 'release';
    public const DESCRIPTION = 'Releases package.';

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

        $this->addArgument('repositoryName', InputArgument::REQUIRED, 'Name of split repository');
        $this->addArgument('branch', InputArgument::REQUIRED, 'Branch');
        $this->addArgument('version', InputArgument::REQUIRED, 'Version');
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
        $branch = $input->getArgument('branch');

        if (!is_string($repositoryName) || !is_string($branch)) {
            throw new InvalidArgumentException('Unsupported type for given argument');
        }

        $this->releaser->release($repositoryName, $branch);

        return 0;
    }
}
