<?php

namespace App\Exceptions;

use Exception;

class BorrowingException extends Exception
{
    public int $statusCode;

    public function __construct(string $message, int $statusCode = 422)
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
    }

    public static function copyNotFound(): self
    {
        return new self('The requested book copy does not exist.', 404);
    }

    public static function copyUnavailable(): self
    {
        return new self('This book copy is not available for borrowing.', 409);
    }

    public static function duplicateActiveBorrow(): self
    {
        return new self('You already have an active loan for this book.', 409);
    }

    public static function transactionNotFound(): self
    {
        return new self('The requested transaction does not exist.', 404);
    }

    public static function unauthorizedAction(): self
    {
        return new self('You are not authorized to perform this action.', 403);
    }

    public static function alreadyReturned(): self
    {
        return new self('This transaction has already been returned.', 409);
    }

    public static function extensionNotAllowed(string $reason): self
    {
        return new self("Loan extension not allowed: {$reason}", 409);
    }
}