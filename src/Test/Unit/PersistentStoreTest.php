<?php

use Ace\Scheduler\Store\PersistentStore;

class PDOMock extends \PDO {
    public function __construct() {}
}

/**
 * @author timrodger
 * Date: 09/12/15
 */
class PersistentStoreTest extends PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider getAddData
     *
     * @param $hour
     * @param $frequency
     * @param $timezone
     */
    public function testSingleAdd($hour, $frequency, $timezone, $expected_hour)
    {
        $client = $this->getMockBuilder('PDOMock')
            ->getMock();

        $store = new PersistentStore($client, 'tasks');
        $name = 'test/test-repo';
        $data = [];

        $mock_statement = $this->getMockBuilder('PDOStatement')
            ->getMock();
        $client->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO :table (name, hour, minute, frequency, timezone, data) VALUES(:name, :hour, :minute, :frequency, :timezone, :data)')
            ->will($this->returnValue($mock_statement));

        $mock_statement->expects($this->once())
            ->method('execute')
            ->with([':table' => 'tasks', ':name' => $name, ':hour' => $expected_hour, ':minute' => 1, ':frequency' => 1, ':timezone' => 'UTC', ':data' => json_encode($data, JSON_UNESCAPED_SLASHES)]);

        $store->add($name, $hour, $frequency, $timezone, $data);
    }

    public function getAddData()
    {
        return [
            ['1', '1', 'UTC', 1],
            ['2', '1', 'CET', 1],
            ['4', '1', 'MSK', 1],
            ['17', '1', 'PDT', 1]
        ];
    }

    public function testGet()
    {
        $expected_hour = 0;
        $expected_min = 1;
        $timestamp = 1;

        $data = ['owner' => 'dave'];

        $client = $this->getMockBuilder('PDOMock')
            ->getMock();

        $store = new PersistentStore($client, 'tasks');
        $result = [
            [
                'name' => 'owner/repo',
                'hour' => $expected_hour,
                'minute' => $expected_min,
                'frequency' => '1',
                'timezone' => 'UTC',
                'data' => json_encode($data, JSON_UNESCAPED_SLASHES)
            ]
        ];

        $mock_statement = $this->getMockBuilder('PDOStatement')
            ->getMock();

        $client->expects($this->once())
            ->method('prepare')
            ->with('SELECT FROM * :table WHERE hour = :hour and minute = :minute')
            ->will($this->returnValue($mock_statement));

        $mock_statement->expects($this->once())
            ->method('execute')
            ->with([':table' => 'tasks', ':hour' => $expected_hour, ':minute' => $expected_min]);

        $mock_statement->expects($this->once())
            ->method('fetchAll')
            ->will($this->returnValue($result));

        $tasks = $store->get($timestamp);

        $this->assertSame([$result[0]['name'] => $result[0]['data']], $tasks);

    }
}
