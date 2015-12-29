<?php

use Ace\Scheduler\Store\RDBMSStore;

class PDOMock extends \PDO {
    public function __construct() {}
}

/**
 * @author timrodger
 * Date: 09/12/15
 */
class RDBMSStoreTest extends PHPUnit_Framework_TestCase
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

        $store = new RDBMSStore($client, 'tasks');
        $url = 'https://github.com/test/test-repo';

        $mock_statement = $this->getMockBuilder('PDOStatement')
            ->getMock();
        $client->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO tasks (url, hour, minute, frequency, timezone) VALUES(:url, :hour, :minute, :frequency, :timezone)')
            ->will($this->returnValue($mock_statement));

        $mock_statement->expects($this->once())
            ->method('execute')
            ->with([':url' => $url, ':hour' => $expected_hour, ':minute' => 1, ':frequency' => 1, ':timezone' => 'UTC']);

        $store->add($url, $hour, $frequency, $timezone);
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

        $url = 'https://github.com/owner/repo';

        $client = $this->getMockBuilder('PDOMock')
            ->getMock();

        $store = new RDBMSStore($client, 'tasks');
        $result = [
            [
                'url' => $url,
                'hour' => $expected_hour,
                'minute' => $expected_min,
                'frequency' => '1',
                'timezone' => 'UTC'
            ]
        ];

        $mock_statement = $this->getMockBuilder('PDOStatement')
            ->getMock();

        $client->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM tasks WHERE hour = :hour and minute = :minute')
            ->will($this->returnValue($mock_statement));

        $mock_statement->expects($this->once())
            ->method('execute')
            ->with([':hour' => $expected_hour, ':minute' => $expected_min]);

        $mock_statement->expects($this->once())
            ->method('fetchAll')
            ->will($this->returnValue($result));

        $tasks = $store->get($timestamp);

        $this->assertSame($result, $tasks);

    }

    public function testGetByUrl()
    {
        $client = $this->getMockBuilder('PDOMock')
            ->getMock();

        $store = new RDBMSStore($client, 'tasks');

        $mock_statement = $this->getMockBuilder('PDOStatement')
            ->getMock();

        $client->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM tasks WHERE url = :url')
            ->will($this->returnValue($mock_statement));

        $mock_statement->expects($this->once())
            ->method('execute')
            ->with([':url' => 'owner/repo']);

        $mock_statement->expects($this->once())
            ->method('fetchAll')
            ->will($this->returnValue(['owner/repo']));

        $task = $store->getByUrl('owner/repo');

        $this->assertSame(['owner/repo'], $task);
    }
}
