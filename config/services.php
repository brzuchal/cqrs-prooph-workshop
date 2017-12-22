<?php declare(strict_types=1);

use App\Application\Command\CreateProduct;
use App\Application\Command\CreateProductHandler;
use App\UI\Command\CreateProductCommand;
use Pimple\Container;
use Pimple\Psr11\Container as PsrContainer;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Container\CommandBusFactory;
use Prooph\ServiceBus\Container\EventBusFactory;
use Prooph\ServiceBus\Container\QueryBusFactory;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\QueryBus;
use Symfony\Component\Console\Application;

return (function (array $config): PsrContainer {
    $container = new Container($config);
    // message bus
    $container['container'] = new PsrContainer($container);
    $container['command_bus'] = static function (Container $container): CommandBus {
        return (new CommandBusFactory())($container['container']);
    };
    $container['event_bus'] = static function (Container $container): EventBus {
        return (new EventBusFactory())($container['container']);
    };
    $container['query_bus'] = static function (Container $container): QueryBus {
        return (new QueryBusFactory())($container['container']);
    };
    // application
    $container[CreateProductHandler::class] = static function (): CreateProductHandler {
        return new CreateProductHandler();
    };
    // command routing
    // ... put come command handlers here
    // ui services
    $container[Application::class] = static function (Container $container): Application {
        $app = new Application();
        $app->addCommands([
            new CreateProductCommand($container['container']),
        ]);
        return $app;
    };
    // return immutable container implementing PSR-11
    return $container['container'];
})([
    'config' => [
        'prooph' => [
            'service_bus' => [
                'command_bus' => [
                    'router' => [
                        'routes' => [
                            CreateProduct::class => CreateProductHandler::class,
                        ],
                    ],
                ],
                'event_bus' => [],
            ],
        ],
    ],
]);
