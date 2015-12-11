<?php namespace Ace\Scheduler\Store;

use PDO;
use DateTime;
use DateTimeZone;

/**
 * @author timrodger
 * Date: 09/12/15
 */
class PersistentStore implements StoreInterface
{

    /**
     * @var PDO
     */
    private $client;

    /**
     * @var string
     */
    private $table_name;

    public function __construct(PDO $client, $table_name)
    {
        $this->client = $client;
        $this->table_name = $table_name;
    }

    /**
     * @param $name
     * @param string $hour
     * @param string $frequency
     * @param string $timezone
     * @param array $data
     */
    public function add($name, $hour, $frequency, $timezone, array $data = [])
    {
        $statement = $this->client->prepare('INSERT INTO ' . $this->table_name . ' (name, hour, minute, frequency, timezone, data) VALUES(:name, :hour, :minute, :frequency, :timezone, :data)');

        // convert $hour in parameter timezone into UTC
        $time = new DateTime(sprintf('%s:00', $hour), new DateTimeZone($timezone));
        $time->setTimezone(new DateTimeZone('UTC'));

        $result = $statement->execute(
            [
                ':name' => $name,
                ':hour' => intval($time->format('H')),
                ':minute' => 1,
                ':frequency' => intval($frequency),
                ':timezone' => 'UTC',
                ':data' => json_encode($data, JSON_UNESCAPED_SLASHES)
            ]
        );

        $statement->closeCursor();

        return $result;
    }

    /**
     * Return all matching tasks matching the parameter timestamp
     *
     * @param $timestamp integer
     *
     * @return array keyed on name
     */
    public function get($timestamp)
    {
        $time = new DateTime();
        $time->setTimestamp($timestamp);
        $time->setTimezone(new DateTimeZone('UTC'));

        $statement = $this->client->prepare('SELECT * FROM ' . $this->table_name . ' WHERE hour = :hour and minute = :minute');

        $statement->execute(
            [
                ':hour' => intval($time->format('H')),
                ':minute' => intval($time->format('m')),
            ]
        );

        $tasks = [];

        $all = $statement->fetchAll();

        foreach ($all as $task) {
            $tasks[] = json_decode($task['data'], true);;
        }

        return $tasks;
    }
}
