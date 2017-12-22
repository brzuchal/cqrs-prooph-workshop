<?php declare(strict_types=1);
namespace App\UI\Command;

use App\Application\Command\CreateProduct;
use Prooph\ServiceBus\CommandBus;
use Psr\Container\ContainerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CreateProductCommand extends Command
{
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;
    }


    protected function configure()
    {
        $this->setName('product:create');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var CommandBus $commandBus */
        $commandBus = $this->container->get('command_bus');

        $createProductCommand = new CreateProduct(Uuid::uuid4(), 'RF379-99X', 'configurable');
        $commandBus->dispatch($createProductCommand);
    }
}
