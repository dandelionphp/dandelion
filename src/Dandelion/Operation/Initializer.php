<?php

namespace Dandelion\Operation;

use Dandelion\Console\Command\InitCommand;
use Dandelion\Exception\RepositoryNotFoundException;

class Initializer extends AbstractOperation implements InitializerInterface
{
    /**
     * @param string $repositoryName
     *
     * @return \Dandelion\Operation\InitializerInterface
     */
    public function executeForSingleRepository(string $repositoryName): InitializerInterface
    {
        $configuration = $this->configurationLoader->load();
        $repositories = $configuration->getRepositories();

        if (!$repositories->offsetExists($repositoryName)) {
            throw new RepositoryNotFoundException(sprintf('Could not find repository "%s".', $repositoryName));
        }

        $platform = $this->platformFactory->create($configuration->getVcs());
        $repository = $repositories->offsetGet($repositoryName);

        if ($platform->existsSplitRepository($repository)) {
            return $this;
        }

        $platform->initSplitRepository($repository);

        return $this;
    }

    /**
     * @param string[] $commandArguments
     *
     * @return string[]
     */
    protected function getCommand(array $commandArguments): array
    {
        return array_merge(
            [
                DANDELION_BINARY,
                InitCommand::NAME,
            ],
            $commandArguments
        );
    }
}
