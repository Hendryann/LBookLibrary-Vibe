<?php

namespace App\Exceptions;

use Exception;

class BookNotFoundException extends Exception
{
    protected $message = 'Book not found.';
    protected $code = 404;
}
