<?php

require_once __DIR__ . '/vendor/autoload.php';

use Silex\Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;

use Ace\Scheduler\Provider\ConfigProvider;
use Ace\Scheduler\Provider\StoreFactoryProvider;
use Ace\Scheduler\Provider\QueueClientProvider;

$app = new Application();

$app['logger'] = new Logger('log');
$app['logger']->pushHandler(new ErrorLogHandler());

$app->register(new ConfigProvider());
$app->register(new StoreFactoryProvider());
$app->register(new QueueClientProvider());

/**
 * Get a schedule
 */
$app->get('/schedules/{repository}', function(Request $request, $repository) use ($app){

    $result = json_encode($app['store-factory']
        ->create()
        ->getByFullName($repository), JSON_UNESCAPED_SLASHES);

    return new Response($result, 200);

})->assert('repository', '.+');

/**
 * Catch exceptions and respond with an appropriate message and status code
 * Works for web requests
 */
$app->error(function (Exception $e, $code) use($app) {
    $app['logger']->addError($e->getMessage());
    $status = ($e->getCode() > 99) ? $e->getCode() : 500;
    return new Response($e->getMessage(), $status);
});

return $app;