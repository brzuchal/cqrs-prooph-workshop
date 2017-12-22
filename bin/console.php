<?php declare(strict_types=1);
namespace App;
require __DIR__ . '/../vendor/autoload.php';

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

/** @var ContainerInterface $container */
$container = (require __DIR__ . '/../config/services.php');
/** @var Application $app */
$app = $container->get(Application::class);
$app->run();
