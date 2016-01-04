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
    public function testSingleAdd($hour, $timezone, $expected_hour)
    {
        $client = $this->getMockBuilder('PDOMock')
            ->getMock();

        $store = new RDBMSStore($client, 'tasks');
        $full_name = 'test/test-repo';

        $mock_statement = $this->getMockBuilder('PDOStatement')
            ->getMock();
        $client->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO tasks (full_name, hour, minute, frequency, timezone) VALUES(:full_name, :hour, :minute, :frequency, :timezone)')
            ->will($this->returnValue($mock_statement));

        $mock_statement->expects($this->once())
            ->method('execute')
            ->with([':full_name' => $full_name, ':hour' => $expected_hour, ':minute' => 1, ':frequency' => 1, ':timezone' => 'UTC']);

        $store->add($full_name, $timezone, $hour, 1);
    }

    /**
     * @dataProvider getAddDefaultData
     * @param $timezone
     * @param $expected_hour
     */
    public function testAddUsesDefaults($timezone, $expected_hour)
    {
        $client = $this->getMockBuilder('PDOMock')
            ->getMock();

        $store = new RDBMSStore($client, 'tasks');
        $full_name = 'test/test-repo';

        $mock_statement = $this->getMockBuilder('PDOStatement')
            ->getMock();
        $client->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO tasks (full_name, hour, minute, frequency, timezone) VALUES(:full_name, :hour, :minute, :frequency, :timezone)')
            ->will($this->returnValue($mock_statement));

        $mock_statement->expects($this->once())
            ->method('execute')
            ->with([':full_name' => $full_name, ':hour' => $expected_hour, ':minute' => 1, ':frequency' => 1, ':timezone' => 'UTC']);

        $store->add($full_name, $timezone);
    }

    public function getAddData()
    {
        return [
            ['1', 'UTC', 1],
            ['1', 'CET', 0],
            ['1', 'MSK', 22],
            ['1', 'PDT', 9]
        ];
    }

    public function getAddDefaultData()
    {
        return [
            ['UTC', 1],
            ['CET', 0],
            ['MSK', 22],
            ['PDT', 9]
        ];
    }

    /**
     * Tests that input timestamp is converted to the correct hour & minute values
     * @dataProvider getTimestampData
     *
     * @param $timestamp
     * @param $expected_hour
     */
    public function testGet($timestamp, $expected_hour)
    {
        $client = $this->getMockBuilder('PDOMock')
            ->getMock();

        $store = new RDBMSStore($client, 'tasks');

        $mock_statement = $this->getMockBuilder('PDOStatement')
            ->getMock();

        $client->expects($this->once())
            ->method('prepare')
            ->will($this->returnValue($mock_statement));

        $mock_statement->expects($this->once())
            ->method('execute')
            ->with([':hour' => $expected_hour, ':minute' => '1']);

        $store->get($timestamp);
    }

    public function getTimestampData()
    {
        return [
            [1, 0],
            [((1 * 60 * 60) + 1), 1],
            [((365 * 60 * 60) + 1), 5],
        ];
    }

    public function testGetByFullName()
    {
        $client = $this->getMockBuilder('PDOMock')
            ->getMock();

        $store = new RDBMSStore($client, 'tasks');

        $mock_statement = $this->getMockBuilder('PDOStatement')
            ->getMock();

        $client->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM tasks WHERE full_name = :full_name')
            ->will($this->returnValue($mock_statement));

        $mock_statement->expects($this->once())
            ->method('execute')
            ->with([':full_name' => 'owner/repo']);

        $mock_statement->expects($this->once())
            ->method('fetchAll')
            ->will($this->returnValue(['owner/repo']));

        $task = $store->getByFullName('owner/repo');

        $this->assertSame(['owner/repo'], $task);
    }
}
