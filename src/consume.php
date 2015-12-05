<?php
/**
 * @author timrodger
 * Date: 05/12/15
 *
 * Consumes events
 * Updates schedule
 *
 */
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$channel_name = 'repo-mon.main';
$queue_host = getenv('RABBITMQ_PORT_5672_TCP_ADDR');
$queue_port = getenv('RABBITMQ_PORT_5672_TCP_ADDR');


$connection = new AMQPStreamConnection($queue_host, $queue_port, 'guest', 'guest');
$channel = $connection->channel();
$channel->queue_declare($channel_name, false, false, false, false);

echo ' [*] Waiting for events. To exit press CTRL+C', "\n";

$callback = function($event) {
    echo " [x] Received ", $event->body, "\n";
};

$channel->basic_consume($channel_name, '', false, true, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();