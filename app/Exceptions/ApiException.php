<?php

namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    public function __construct(
        private readonly string $error,
        string $message,
        private readonly int $status = 400,
        private readonly mixed $details = null
    ) {
        parent::__construct($message);
    }

    public function error(): string
    {
        return $this->error;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function details(): mixed
    {
        return $this->details;
    }
}
