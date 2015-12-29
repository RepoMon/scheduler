<?php

require_once __DIR__ . '/vendor/autoload.php';

use Silex\Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;

use Ace\Scheduler\Provider\ConfigProvider;
use Ace\Scheduler\Provider\StoreProvider;
use Ace\Scheduler\Provider\QueueClientProvider;

$app = new Application();

$app['logger'] = new Logger('log');
$app['logger']->pushHandler(new ErrorLogHandler());

$app->register(new ConfigProvider());
$app->register(new StoreProvider());
$app->register(new QueueClientProvider());


/**
 * Get a schedule
 */
$app->get('/schedules/{repository}', function(Request $request, $repository) use ($app){

    $result = json_encode($app['store']->getByUrl($repository), JSON_UNESCAPED_SLASHES);

    return new Response($result, 200);

})->assert('repository', '.+');

/**
 * Remove a schedule - should be performed by consuming an event
 * no need for an endpoint
 */
$app->delete('/schedules/{repository}', function(Request $request, $repository) use ($app){

    $app['store']->delete($repository);

    return new Response('', 200);

})->assert('repository', '.+');

/**
 */
$app->error(function (Exception $e, $code) use($app) {
    $app['logger']->addError($e->getMessage());
    return new Response($e->getMessage(), $e->getCode() ? ($e->getCode() > 99) : 500);
});

return $app;