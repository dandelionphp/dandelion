<?php

namespace Dandelion\Console\Command;

use Dandelion\Configuration\ConfigurationValidatorInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function sprintf;

class ValidateCommand extends Command
{
    public const NAME = 'validate';
    public const DESCRIPTION = 'Validates dandelion.json.';
    /**
     * @var \Dandelion\Configuration\ConfigurationValidatorInterface
     */
    protected $configurationValidator;

    public function __construct(ConfigurationValidatorInterface $configurationValidator)
    {
        parent::__construct();
        $this->configurationValidator = $configurationValidator;
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
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->configurationValidator->validate();
        } catch (Exception $e) {
            $output->write(sprintf('<error>%s</error>', $e->getMessage()));
            return 1;
        }

        $output->write(sprintf('<info>%s</info>', 'Configuration is valid.'));

        return 0;
    }
}