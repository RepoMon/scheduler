<?php
/**
 * @author timrodger
 *
 * Consumes repo-mon.repo.configured events
 * Updates schedule
 */

$app = require_once __DIR__ .'/app.php';
$app->boot();

printf("rabbit host: %s port: %s channel: %s\n",
    $app['config']->getRabbitHost(),
    $app['config']->getRabbitPort(),
    $app['config']->getRabbitChannelName()
);

$store = $app['store'];

$callback = function($event) use ($store) {

    echo " Received ", $event->body, "\n";

    $event = json_decode($event->body, true);

    // overwrite any existing configuration
    if ($event['name'] === 'repo-mon.repo.activated') {

        $store->delete($event['data']['full_name']);

        $result = $store->add(
            $event['data']['full_name'],
            $event['data']['hour'],
            $event['data']['frequency'],
            $event['data']['timezone']
        );

        echo " Result of insert is '$result'\n";

    } else if ($event['name'] === 'repo-mon.repo.deactivated') {

        $result = $store->delete($event['data']['full_name']);
        echo " Result of delete is '$result'\n";
    }
};

$app['queue-client']->consume($callback);
