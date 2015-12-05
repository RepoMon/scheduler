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

echo "Publishing schedule events\n";

// get scheduled tasks from store that should be run now

// publish events, one per task
$channel_name = 'repo-mon.main';
$queue_host = getenv('RABBITMQ_PORT_5672_TCP_ADDR');
$queue_port = getenv('RABBITMQ_PORT_5672_TCP_PORT');

$connection = new AMQPStreamConnection($queue_host, $queue_port, 'guest', 'guest');
$channel = $connection->channel();
$channel->queue_declare($channel_name, false, false, false, false);

$event = [
    'name' => 'repo-mon.repo.update.scheduled',
    'data' => [
        'repository' => 'test/test'
    ]
];

$msg = new AMQPMessage(json_encode($event, JSON_UNESCAPED_SLASHES), [
    'content_type' => 'application/json',
    'timestamp' => time()
]);

$channel->basic_publish($msg, '', $this->channel_name);

$channel->close();
$connection->close();
