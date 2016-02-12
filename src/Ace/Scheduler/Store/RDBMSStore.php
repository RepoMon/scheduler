<?php namespace Ace\Scheduler\Store;

use Ace\Scheduler\Exception\NotFoundException;
use PDO;
use DateTime;
use DateTimeZone;
use Exception;

/**
 * @author timrodger
 * Date: 09/12/15
 */
class RDBMSStore implements StoreInterface
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
     * @param string $full_name
     * @param string $timezone
     * @param int $hour
     * @param int $frequency
     * @return bool
     */
    public function add($full_name, $timezone, $hour = 1, $frequency = 1)
    {
        $statement = $this->client->prepare('INSERT INTO ' . $this->table_name . ' (full_name, hour, minute, frequency, timezone) VALUES(:full_name, :hour, :minute, :frequency, :timezone)');

        // convert $hour in parameter timezone into UTC
        $time = new DateTime(sprintf('%s:00', $hour), new DateTimeZone($timezone));
        $time->setTimezone(new DateTimeZone('UTC'));

        $result = $statement->execute(
            [
                ':full_name' => $full_name,
                ':hour' => intval($time->format('H')),
                ':minute' => 1,
                ':frequency' => intval($frequency),
                ':timezone' => $timezone
            ]
        );

        $statement->closeCursor();

        return $result;
    }

    /**
     * Return all matching tasks matching the parameter timestamp
     *
     * @param $timestamp integer unix timestamp
     *
     * @return array keyed on url
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
                ':minute' => intval($time->format('i')),
            ]
        );

        $result = $statement->fetchAll();
        $statement->closeCursor();
        return $result;
    }

    /**
     * @param $url
     * @return array
     */
    public function getByFullName($full_name)
    {
        $statement = $this->client->prepare('SELECT * FROM ' . $this->table_name . ' WHERE full_name = :full_name');
        $statement->execute(
            [
                ':full_name' => $full_name
            ]
        );

        $all = $statement->fetchAll();
        $statement->closeCursor();

        if (!count($all)){
            throw new NotFoundException("No schedules found for '$full_name'");
        }

        return $all;
    }

    /**
     * @param $url
     * @return mixed
     */
    public function delete($full_name)
    {
        $statement = $this->client->prepare('DELETE FROM ' . $this->table_name . ' WHERE full_name = :full_name');
        $statement->execute(
            [
                ':full_name' => $full_name
            ]
        );
        $statement->closeCursor();
    }
}
