<?php

namespace App\Exceptions;

use Exception;

class ReviewNotFoundException extends Exception
{
    protected $message = 'Review not found.';
    protected $code = 404;
}
