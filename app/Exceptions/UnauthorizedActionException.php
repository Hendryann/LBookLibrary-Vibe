<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class UnauthorizedActionException extends Exception
{
    public function __construct(string $message = 'You are not authorized to perform this action.', Throwable $previous = null)
    {
        parent::__construct($message, 403, $previous);
    }
}
