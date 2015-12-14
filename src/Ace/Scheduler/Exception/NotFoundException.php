<?php namespace Ace\Scheduler\Exception;

use RuntimeException;

class NotFoundException extends RuntimeException
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, 404);
    }
}
