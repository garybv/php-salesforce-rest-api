<?php

namespace bizmatesinc\SalesForce\Exception;

use Throwable;

class AuthenticationFailed extends SalesForceException
{
    public function __construct($message = 'SalesForce authentication failed', $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
