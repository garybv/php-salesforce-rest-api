<?php

namespace bizmatesinc\SalesForce\Exception;

use Throwable;

class ApiNotInitialized extends SalesForceException
{
    public function __construct($message = 'SalesForce API was not initialized. All subsequent calls will fail.', $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
