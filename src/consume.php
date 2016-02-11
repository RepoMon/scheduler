<?php
/**
 * @author timrodger
 *
 * Consumes repo-mon.repo.activated, deactivated and removed events
 *
 * Updates schedule on consuming events
 */

$app = require_once __DIR__ .'/app.php';
$app->boot();

printf("rabbit host: %s port: %s channel: %s\n",
    $app['config']->getRabbitHost(),
    $app['config']->getRabbitPort(),
    $app['config']->getRabbitChannelName()
);

$store_factory = $app['store-factory'];

$addHandler = function($event) use ($store_factory) {

    $store = $store_factory->create();

    $store->delete($event['data']['full_name']);

    $result = $store->add(
        $event['data']['full_name'],
        $event['data']['timezone']
    );

    echo " Result of insert is '$result'\n";
};

$removeHandler = function($event) use ($store_factory) {

    $store = $store_factory->create();
    $result = $store->delete($event['data']['full_name']);

    echo " Result of delete is '$result'\n";
};

$app['queue-client']->addEventHandler('repo-mon.repository.activated', $addHandler);

$app['queue-client']->addEventHandler('repo-mon.repository.deactivated', $removeHandler);

$app['queue-client']->addEventHandler('repo-mon.repository.removed', $removeHandler);

$app['queue-client']->consume();
