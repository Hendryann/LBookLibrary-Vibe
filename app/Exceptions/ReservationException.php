<?php

namespace App\Exceptions;

use Exception;

class ReservationException extends Exception
{
    protected int $statusCode;

    public function __construct(string $message, int $statusCode = 422)
    {
        parent::__construct($message);

        $this->statusCode = $statusCode;
    }

    public function status(): int
    {
        return $this->statusCode;
    }

    public static function bookNotFound(): self
    {
        return new self('The requested book does not exist.', 404);
    }

    public static function reservationNotFound(): self
    {
        return new self('The requested reservation does not exist.', 404);
    }

    public static function availableCopiesExist(): self
    {
        return new self('This book currently has available copies and cannot be reserved.', 409);
    }

    public static function duplicateReservation(): self
    {
        return new self('You already have an active reservation for this book.', 409);
    }

    public static function unauthorized(): self
    {
        return new self('You are not authorized to perform this action.', 403);
    }

    public static function alreadyCancelled(): self
    {
        return new self('This reservation has already been cancelled.', 409);
    }

    public static function alreadyFulfilled(): self
    {
        return new self('This reservation has already been fulfilled and cannot be cancelled.', 409);
    }
}
