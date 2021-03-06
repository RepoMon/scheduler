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
     * @param $full_name
     * @param $timezone
     * @param int $hour
     * @param int $frequency
     * @return bool
     */
    public function add($full_name, $timezone, $hour = 1, $frequency = 1);

    /**
     * Return all matching tasks matching the parameter timestamp
     *
     * @param integer $timestamp
     *
     * @return array keyed on name
     */
    public function get($timestamp);

    /**
     * @param $full_name
     * @return boolean
     */
    public function getByFullName($full_name);

    /**
     * @param $name
     * @return mixed
     */
    public function delete($name);
}