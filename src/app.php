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
 * List all schedules
 */
$app->get('/', function(Request $request) use ($app){

    return new Response(
        json_encode($app['store']->filter('*'), JSON_UNESCAPED_SLASHES),
        200
    );
});

/**
 */
$app->error(function (Exception $e, $code) use($app) {
    $app['logger']->addError($e->getMessage());
    return new Response($e->getMessage(), $e->getCode());
});

return $app;