# CQRS Workshop

## Project initialization

Build PHP container:
```bash
export UID
docker-compose build
```
> Note: Exporting UID is required because UID is shell variable not environment variable!

Run bash in PHP Docker container
```bash
docker-compose run php bash
```
> Note: Now we're running bash as php user with same rights as on host machine.

Initialize composer and add some requirements
```bash
composer init --quiet
mkdir bin
composer config bin-dir bin/
composer require ramsey/uuid psr/container prooph/service-bus symfony/console
composer require --dev phpunit/phpunit friendsofphp/php-cs-fixer
```

## Setting up project

Create `src` directory and `.gitignore`
```bash
mkdir src
echo 'bin' > .gitignore
echo '!bin/console' >> .gitignore
echo '!bin/console.php' >> .gitignore
echo 'vendor' >> .gitignore
```

Add preferred autoload to `composer.json` for eg. PSR-4
```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
```

After that we need to dump autoload
```bash
composer dump-autoload
```

Create console application in `bin/console`
```bash
touch bin/console
chmod +x bin/console
```

Which proxies execution to PHP script `bin/console.php` with content
```bash
#!/usr/bin/env php
<?php require __DIR__ . '/console.php';
```

And put content of `bin/console.php`
```php
<?php declare(strict_types=1);
namespace App;
require __DIR__ . '/../vendor/autoload.php';
use Symfony\Component\Console\Application;

$app = new Application();

// ... register commands

$app->run();
```

Test if `bin/console` is running properly
```bash
bin/console
```


## Setting up some services

We're gonna need additional requirements
```bash
composer require pimple/pimple sandrokeil/interop-config
composer require --dev symfony/var-dumper
```

Put message bus services into container `config/services.php`
```php
<?php declare(strict_types=1);

use Pimple\Container;
use Pimple\Psr11\Container as PsrContainer;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Container\CommandBusFactory;
use Prooph\ServiceBus\Container\EventBusFactory;
use Prooph\ServiceBus\Container\QueryBusFactory;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\QueryBus;
use Symfony\Component\Console\Application;
use App\UI\Command\CreateProductCommand;

return (function (): Container {
    $container = new Container();
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
})();
```
> Note: we used IIFE to stop exposing `$container` variable in `$GLOBALS` table

Now we're gonna create first command+handler and CLI command

Create Symfony Command with content
```php
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
```

Create application command
```php
<?php declare(strict_types=1);
namespace App\Application\Command;

use Ramsey\Uuid\UuidInterface;

final class CreateProduct
{
    /** @var UuidInterface */
    private $id;
    /** @var string */
    private $sku;
    /** @var string */
    private $type;

    public function __construct(UuidInterface $id, string $sku, string $type)
    {
        $this->id = $id;
        $this->sku = $sku;
        $this->type = $type;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
```

And associated handler
```php
<?php declare(strict_types=1);
namespace App\Application\Command;

final class CreateProductHandler
{
    public function __invoke(CreateProduct $command) : void
    {
        dump($command);
    }
}
```

Add routing to command bus configuration under `config.prooph.command_bus.router` path
```php
[
    'routes' => [
        // here'll be commands to handlers routing
        \App\Application\Command\CreateProduct::class => \App\Application\Command\CreateProductHandler::class,
    ],
]
```

And test it using CLI
```bash
bin/console product:create
```
