<?php namespace Ace\Scheduler\Store; 
/**
 * @author timrodger
 * Date: 12/12/15
 */
interface StoreFactoryInterface
{

    /**
     * @return \Ace\Scheduler\Store\StoreInterface
     */
    public function create();
}