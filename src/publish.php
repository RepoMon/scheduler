<?php
/**
 * @author timrodger
 *
 * Checks schedule
 * Publishes repo-mon.update.scheduled events
 */

$app = require_once __DIR__ .'/app.php';
$app->boot();

$now = time();

printf("rabbit host: %s port: %s channel: %s\n",
    $app['config']->getRabbitHost(),
    $app['config']->getRabbitPort(),
    $app['config']->getRabbitChannelName()
);

$event = [
    'name' => 'repo-mon.scheduler.heartbeat',
    'data' => [
        'time' => $now
    ]
];
$app['queue-client']->publish($event);

$tasks = $app['store']->get($now);

foreach ($tasks as $task) {

    $event = [
        'name' => 'repo-mon.update.scheduled',
        'data' => ['full_name' => $task['full_name']]
    ];
    $app['queue-client']->publish($event);
}
