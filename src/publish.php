<?php
/**
 * @author timrodger
 * Date: 05/12/15
 *
 * Run by cron tab
 * Checks schedule
 * Publishes events
 */

require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

echo "Publishing schedule events\n";

// get scheduled tasks from store that should be run now

// publish events, one per task
$channel_name = 'repo-mon.main';

// this script runs from cron with a different env to consume.php script
// use the entry in /etc/hosts to access the rabbit mq server

// use the hostname
$queue_host = 'rabbitmq';
$queue_port = 5672;

$connection = new AMQPStreamConnection($queue_host, $queue_port, 'guest', 'guest');
$channel = $connection->channel();
$channel->exchange_declare($channel_name, 'fanout', false, false, false);

$event = [
    'name' => 'repo-mon.repo.scheduler.heartbeat',
    'data' => [
        'repository' => 'test/test'
    ]
];

$msg = new AMQPMessage(json_encode($event, JSON_UNESCAPED_SLASHES), [
    'content_type' => 'application/json',
    'timestamp' => time()
]);

$channel->basic_publish($msg, $channel_name);

$channel->close();
$connection->close();
