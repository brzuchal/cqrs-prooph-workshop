# CQRS Workshop

## Project initialization

Building PHP container:
```
export UID
docker-compose build
```
> Note: Exporting UID is required because UID is shell variable not environment variable!

Running bash in PHP container
```
docker-compose run php bash
```
> Note: Now you're running bash as php user with same rights as on host machine.

Running composer initialization
```
composer init --quiet
mkdir bin
composer config bin-dir bin/
composer require ramsey/uuid psr/container prooph/service-bus symfony/console
composer require --dev phpunit/phpunit friendsofphp/php-cs-fixer
```

## Setting up project

Creating src directory and .gitignore
```
mkdir src
echo 'bin' > .gitignore
echo '!bin/console' >> .gitignore
echo 'vendor' >> .gitignore
```

Adding autoload to `composer.json`
```
{
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
```

Dumping autoload
```
composer dump-autoload
```

Creating bin/console
```
touch bin/console
chmod +x bin/console
```

Creating Symfony Console app
```
#!/usr/bin/env php
<?php declare(strict_types=1);
namespace App;
require __DIR__ . '/../vendor/autoload.php';
use Symfony\Component\Console\Application;

$app = new Application();

// ... register commands

$app->run();
```
