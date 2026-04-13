<?php
declare(strict_types=1);

namespace Christo\PlentificApi\Exception;

use RuntimeException;
use Throwable;

class ApiException extends RuntimeException
{
    public static function fromThrowable(
        Throwable $throwable,
        string $message = 'The user API request failed.',
    ): self {
        return new self($message, 0, $throwable);
    }
}


