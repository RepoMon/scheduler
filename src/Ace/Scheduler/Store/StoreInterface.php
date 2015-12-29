<?php namespace Ace\Scheduler\Store;

/**
 * Store persists data and scheduling information
 * For a given hour and timezone it picks the best minute and uses UTC as the standard timezone
 *
 * @author timrodger
 * Date: 09/12/15
 */
interface StoreInterface
{
    /**
     * @param $url string
     * @param $hour string
     * @param $frequency string
     * @param $timezone string
     * @return null
     */
    public function add($url, $hour, $frequency, $timezone);

    /**
     * Return all matching tasks matching the parameter timestamp
     *
     * @param $timestamp integer
     *
     * @return array keyed on name
     */
    public function get($timestamp);

    /**
     * @param $url
     * @return boolean
     */
    public function getByUrl($url);

    /**
     * @param $query
     * @return array
     */
    public function filter($query);

    /**
     * @param $name
     * @return mixed
     */
    public function delete($name);
}