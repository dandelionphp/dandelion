<?php

namespace Dandelion\Console\Command;

use Dandelion\Operation\InitializerInterface;
use Dandelion\Operation\Result\MessageInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeCommand extends Command
{
    public const NAME = 'init';
    public const DESCRIPTION = 'Initialize all repos'; // TODO: Add better description

    /**
     * @var \Dandelion\Operation\AbstractOperation
     */
    protected $splitter;
    /**
     * @var \Dandelion\Operation\InitializerInterface
     */
    private $initializer;

    /**
     * @param \Dandelion\Operation\InitializerInterface $initializer
     */
    public function __construct(
        InitializerInterface $initializer
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
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Initialize...');
        $output->writeln('---------------------------------');

        $output->writeln('Add Git remotes:');
        $result = $this->initializer->addGitRemotes();

        foreach ($result->getMessages() as $message) {
            $symbol = $message->getType() === MessageInterface::TYPE_INFO ? '<fg=green>✔</>' : '<fg=red>✗</>';
            $output->writeln(sprintf('%s %s', $symbol, $message->getText()));
        }

        $output->writeln('---------------------------------');
        $output->writeln('Finished');


        return 0;
    }
}
