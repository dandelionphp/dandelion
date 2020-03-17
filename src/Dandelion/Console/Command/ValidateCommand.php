<?php

namespace Dandelion\Console\Command;

use Dandelion\Configuration\ConfigurationValidatorInterface;
use Dandelion\Exception\ConfigurationNotValidException;
use Exception;
use Psr\Log\LoggerInterface;
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

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Dandelion\Configuration\ConfigurationValidatorInterface $configurationValidator
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        ConfigurationValidatorInterface $configurationValidator,
        LoggerInterface $logger
    ) {
        parent::__construct();
        $this->configurationValidator = $configurationValidator;
        $this->logger = $logger;
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
        try {
            $this->configurationValidator->validate();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return 1;
        }

        $this->logger->info('Configuration is valid.');
        return 0;
    }
}
