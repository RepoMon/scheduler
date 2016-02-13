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

$app['queue-client']->publish(
    [
        'name' => 'repo-mon.scheduler.heartbeat',
        'data' => [
            'time' => $now
        ],
        'version' => '1.0.0'
    ]
);

$store = $app['store-factory']->create();

$tasks = $store->get($now);

foreach ($tasks as $task) {
    $app['queue-client']->publish(
        [
            'name' => 'repo-mon.update.scheduled',
            'data' => [
                'full_name' => $task['full_name']
            ],
            'version' => '1.0.0'
        ]
    );
}
