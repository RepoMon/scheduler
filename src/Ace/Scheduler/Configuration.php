<?php namespace Ace\Scheduler;

/*
 * @author tim rodger
 * Date: 29/11/15
 */
class Configuration
{
    /**
     * @return string
     */
    public function getDbUser()
    {
        return 'root';
    }

    /**
     * @return string
     */
    public function getDbPassword()
    {
        // env vars not available on publish
        return '1234';
        //return getenv('MYSQL_ROOT_PASSWORD');
    }

    public function getDbHost()
    {
        return 'mysql';
    }

    public function getDbName()
    {
        return 'scheduler';
    }

    /**
     * @return string
     */
    public function getRabbitHost()
    {
        return getenv('RABBITMQ_PORT_5672_TCP_ADDR');
    }

    /**
     * @return string
     */
    public function getRabbitPort()
    {
        return getenv('RABBITMQ_PORT_5672_TCP_PORT');
    }

    /**
     * @return string
     */
    public function getRabbitChannelName()
    {
        // use an env var for the channel name too
        return 'repo-mon.main';
    }
}