<?php

namespace bizmatesinc\SalesForce\Exception;

use Throwable;

class UnexpectedJsonFormat extends SalesForceException
{
    public function __construct($message = 'Unexpected JSON response from SalesForce', $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
