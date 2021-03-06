<?php declare(strict_types=1);

namespace Symplify\Statie\DependencyInjection\CompilerPass;

use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symplify\PackageBuilder\DependencyInjection\DefinitionCollector;
use Symplify\Statie\Contract\Templating\FilterProviderInterface;
use Symplify\Statie\FlatWhite\Latte\LatteFactory;

final class CollectorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder): void
    {
        $this->collectCommandsToConsoleApplication($containerBuilder);
        $this->loadFilterProvidersToLatteFactory($containerBuilder);
    }

    private function collectCommandsToConsoleApplication(ContainerBuilder $containerBuilder): void
    {
        DefinitionCollector::loadCollectorWithType(
            $containerBuilder,
            ConsoleApplication::class,
            Command::class,
            'add'
        );
    }

    private function loadFilterProvidersToLatteFactory(ContainerBuilder $containerBuilder): void
    {
        DefinitionCollector::loadCollectorWithType(
            $containerBuilder,
            LatteFactory::class,
            FilterProviderInterface::class,
            'addFilterProvider'
        );
    }
}
