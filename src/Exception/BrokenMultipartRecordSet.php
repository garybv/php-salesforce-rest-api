<?php

namespace bizmatesinc\SalesForce\Exception;

use Throwable;

class BrokenMultipartRecordSet extends SalesForceException
{
    public function __construct($message = 'SalesForce returned a partial record set without a reference to the next part', $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
