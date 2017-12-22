<?php declare(strict_types=1);
namespace App\Application\Command;

final class CreateProductHandler
{
    public function __invoke(CreateProduct $command) : void
    {
        dump($command);
    }
}