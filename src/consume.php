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

$callback = function($event) use ($store) {

    echo " Received ", $event->body, "\n";

    $event = json_decode($event->body, true);

    if ($event['name'] === 'repo-mon.repo.configured') {
        $result = $store->add(
            $event['data']['url'],
            $event['data']['hour'],
            $event['data']['frequency'],
            $event['data']['timezone'],
            [
                'owner' => $event['data']['owner'],
                'language' => $event['data']['language'],
                'dependency_manager' => $event['data']['dependency_manager'],
                'url' => $event['data']['url']
            ]
        );

        echo " Result of insert is '$result'\n";
    }
};

$app['queue-client']->consume($callback);
