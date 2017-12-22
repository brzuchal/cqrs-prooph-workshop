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
