<?php

namespace Kaninstein\LaravelAppErrors;

use RuntimeException;
use Throwable;

final class AppErrorException extends RuntimeException
{
    public function __construct(
        public readonly AppError $appError,
        ?Throwable $previous = null,
    ) {
        parent::__construct($appError->message, 0, $previous);
    }
}

