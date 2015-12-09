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

    public function __construct(PDO $client)
    {
        $this->client = $client;
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
        $statement = $this->client->prepare('INSERT INTO schedule (name, hour, minute, $frequency, timezone, data) VALUES(:name, :hour, :minute, :frequency, :timezone, :data)');

        // convert $hour in parameter timezone into UTC
        $time = new DateTime(sprintf('%s:00', $hour), new DateTimeZone($timezone));
        $time->setTimezone(new DateTimeZone('UTC'));

        $statement->execute(
            [
                ':name' => $name,
                ':hour' => $time->format('H'),
                ':minute' => '1',
                ':timezone' => 'UTC',
                ':data' => json_encode($data, JSON_UNESCAPED_SLASHES)
            ]
        );
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

        $statement = $this->client->prepare('SELECT FROM * schedule WHERE hour = :hour and minute = :minute');

        $statement->execute([
            ':hour' => $time->format('H'),
            ':minute' => $time->format('m'),
        ]);

        $tasks = [];
        $all = $statement->fetchAll();

        foreach ($all as $task) {
            $tasks[$task['name']] = $task['data'];
        }

        return $tasks;
    }
}