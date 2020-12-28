<?php

namespace Dandelion\Operation;

use Dandelion\Console\Command\SplitRepositoryInitCommand;
use Dandelion\Exception\RepositoryNotFoundException;

class SplitRepositoryInitializer extends AbstractOperation implements SplitRepositoryInitializerInterface
{
    /**
     * @param string $repositoryName
     *
     * @return \Dandelion\Operation\SplitRepositoryInitializerInterface
     */
    public function executeForSingleRepository(string $repositoryName): SplitRepositoryInitializerInterface
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
                SplitRepositoryInitCommand::NAME,
            ],
            $commandArguments
        );
    }
}
