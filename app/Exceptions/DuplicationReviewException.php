<?php

namespace App\Exceptions;

use Exception;

class DuplicateReviewException extends Exception
{
    protected $message = 'You have already reviewed this book.';
    protected $code = 409;
}
