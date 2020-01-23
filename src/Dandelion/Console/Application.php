<?php

declare(strict_types=1);

namespace Dandelion\Console;

use Pimple\Container;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;

use function count;
use function is_array;

class Application extends SymfonyApplication
{
    public const NAME = 'Monorepo';
    public const VERSION = '1.0.0';

    /**
     * @var \Pimple\Container
     */
    protected $container;

    /**
     * @param \Pimple\Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct(static::NAME, static::VERSION);

        $this->container = $container;
    }

    /**
     * @return \Symfony\Component\Console\Command\Command[]
     */
    protected function getDefaultCommands(): array
    {
        $defaultCommands = parent::getDefaultCommands();

        if (!$this->container->offsetExists('commands')) {
            return $defaultCommands;
        }

        $commandsToAdd = $this->container->offsetGet('commands');

        if (!is_array($commandsToAdd) || count($commandsToAdd) === 0) {
            return $defaultCommands;
        }

        foreach ($commandsToAdd as $command) {
            if (!$command instanceof Command) {
                continue;
            }

            $defaultCommands[] = $command;
        }

        return $defaultCommands;
    }
}
