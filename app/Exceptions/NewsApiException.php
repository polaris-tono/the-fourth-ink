<?php

namespace App\Exceptions;

use RuntimeException;

class NewsApiException extends RuntimeException
{
    /** A fatal error (bad key, rate limit, service down) means we should stop sending more requests. */
    public function __construct(string $message, public readonly bool $fatal = false)
    {
        parent::__construct($message);
    }
}