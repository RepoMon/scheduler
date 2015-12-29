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
     * @param string $url
     * @param string $hour
     * @param string $frequency
     * @param string $timezone
     * @param array $data
     */
    public function add($url, $hour, $frequency, $timezone)
    {
        $statement = $this->client->prepare('INSERT INTO ' . $this->table_name . ' (url, hour, minute, frequency, timezone) VALUES(:url, :hour, :minute, :frequency, :timezone)');

        // convert $hour in parameter timezone into UTC
        $time = new DateTime(sprintf('%s:00', $hour), new DateTimeZone($timezone));
        $time->setTimezone(new DateTimeZone('UTC'));

        // pick minute for schedule based on current tasks at this hour
        $result = $statement->execute(
            [
                ':url' => $url,
                ':hour' => intval($time->format('H')),
                ':minute' => 1,
                ':frequency' => intval($frequency),
                ':timezone' => 'UTC'
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
                ':minute' => intval($time->format('m')),
            ]
        );

        return $statement->fetchAll();
    }

    /**
     * @param $url
     * @return array
     */
    public function getByUrl($url)
    {
        $statement = $this->client->prepare('SELECT * FROM ' . $this->table_name . ' WHERE url = :url');
        $statement->execute(
            [
                ':url' => $url
            ]
        );

        $all = $statement->fetchAll();
        if (!count($all)){
            throw new NotFoundException("No schedules found for '$url'");
        }

        return $all;
    }

    /**
     * @param $query
     * @return array
     */
    public function filter($query)
    {
    }

    /**
     * @param $url
     * @return mixed
     */
    public function delete($url)
    {
        $statement = $this->client->prepare('DELETE FROM ' . $this->table_name . ' WHERE url = :url');
        $statement->execute(
            [
                ':url' => $url
            ]
        );
    }
}
