<?php namespace Ace\Scheduler\Store;

use PDO;

/**
 * @author timrodger
 * Date: 10/12/15
 */
class StoreFactory
{
    /**
     * @var string
     */
    private $db_host;

    /**
     * @var string
     */
    private $db_name;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    private $table_name = 'tasks';

    /**
     * @param $db_host
     * @param $db_name
     * @param $user
     * @param $password
     */
    public function __construct($db_host, $db_name, $user, $password)
    {
        $this->db_host = $db_host;
        $this->db_name = $db_name;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * @return StoreInterface
     */
    public function create()
    {
        $dsn = sprintf('mysql:host=%s', $this->db_host);
        $pdo = new PDO($dsn, $this->user, $this->password);

        // ensure db exists
        $pdo->query(sprintf('CREATE DATABASE IF NOT EXISTS %s', $this->db_name));
        $pdo->query(sprintf('use %s', $this->db_name));

        // next ensure table exists
        $pdo->query(sprintf('CREATE TABLE IF NOT EXISTS %s (name TEXT, hour INT, minute INT, frequency INT, timezone TEXT, data TEXT)', $this->table_name));

        return new PersistentStore($pdo, $this->table_name);
    }
}