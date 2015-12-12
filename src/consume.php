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

use Ace\Scheduler\Configuration;
use Ace\Scheduler\Store\RDBMSStoreFactory;
use PhpAmqpLib\Connection\AMQPStreamConnection;

$config = new Configuration();

printf(" rabbit host %s port %s\n", $config->getRabbitHost(), $config->getRabbitPort());
echo "MYSQL_ROOT_PASSWORD " . getenv('MYSQL_ROOT_PASSWORD') . "\n";

$connection = new AMQPStreamConnection($config->getRabbitHost(), $config->getRabbitPort(), 'guest', 'guest');
$channel = $connection->channel();
$channel->exchange_declare($config->getRabbitChannelName(), 'fanout', false, false, false);

list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);

$channel->queue_bind($queue_name, $config->getRabbitChannelName());

echo ' Waiting for events. To exit press CTRL+C', "\n";

$factory = new RDBMSStoreFactory(
    $config->getDbHost(),
    $config->getDbName(),
    $config->getDbUser(),
    $config->getDbPassword()
);

$store = $factory->create();

// ensure the database exists

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

$channel->basic_consume($queue_name, '', false, true, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();