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

//file_put_contents(
//        "/tmp/publish.log",
//        sprintf("rabbit host: %s port: %s channel: %s at %s\n",
//            $app['config']->getRabbitHost(),
//            $app['config']->getRabbitPort(),
//            $app['config']->getRabbitChannelName(),
//            date('c', $now)
//        ),
//        FILE_APPEND
//);

$event = [
    'name' => 'repo-mon.scheduler.heartbeat',
    'data' => [
        'time' => $now
    ]
];
$app['queue-client']->publish($event);

$tasks = $app['store']->get($now);

foreach ($tasks as $task) {
    $app['queue-client']->publish(
        [
            'name' => 'repo-mon.update.scheduled',
            'data' => [
                'full_name' => $task['full_name']
            ]
        ]
    );
}
