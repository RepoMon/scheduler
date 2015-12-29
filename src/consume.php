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
    if ($event['name'] === 'repo-mon.repo.configured') {

        $store->delete($event['data']['url']);

        $result = $store->add(
            $event['data']['url'],
            $event['data']['hour'],
            $event['data']['frequency'],
            $event['data']['timezone']
        );

        echo " Result of insert is '$result'\n";
    } else if ($event['name'] === 'repo-mon.repo.unconfigured') {
        $result = $store->delete($event['data']['url']);
        echo " Result of delete is '$result'\n";
    }
};

$app['queue-client']->consume($callback);
