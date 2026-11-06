<?php

namespace App\Exceptions;

use RuntimeException;

class BusinessException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $statusCode = 409,
    ) {
        parent::__construct($message);
    }
}