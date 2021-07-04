<?php

declare(strict_types=1);

namespace Dandelion\Console\Command;

use Dandelion\Merger\MergerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MergeCommand extends Command
{
    public const NAME = 'merge';
    public const DESCRIPTION = 'Merge package.';

    /**
     * @var \Dandelion\Merger\MergerInterface
     */
    protected $merger;

    /**
     * MergeCommand constructor.
     *
     * @param \Dandelion\Merger\MergerInterface $merger
     */
    public function __construct(
        MergerInterface $merger
    ) {
        parent::__construct();
        $this->merger = $merger;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName(static::NAME);
        $this->setDescription(static::DESCRIPTION);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->merger->merge();

        return 0;
    }
}
