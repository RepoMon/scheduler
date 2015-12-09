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
     * @param $repo string
     * @param $hour string
     * @param $frequency string
     * @param $timezone string
     * @param $data array
     * @return null
     */
    public function add($name, $hour, $frequency, $timezone, array $data = []);

    /**
     * Return all matching tasks matching the parameter timestamp
     *
     * @param $timestamp integer
     *
     * @return array keyed on name
     */
    public function get($timestamp);
}