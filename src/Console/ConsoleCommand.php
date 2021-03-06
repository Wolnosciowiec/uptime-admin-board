<?php declare(strict_types=1);

namespace Riotkit\HealthFlux\Console;

use Psr\Container\ContainerInterface;
use Riotkit\HealthFlux\Kernel;
use Symfony\Component\Console\Command\Command;

abstract class ConsoleCommand extends Command
{
    protected Kernel $app;
    protected ContainerInterface $container;

    public function __construct(string $name = null)
    {
        parent::__construct($name);

        $this->app = new Kernel();
        $this->container = $this->app->getContainer();
    }
}
