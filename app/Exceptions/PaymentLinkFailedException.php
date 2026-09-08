<?php

namespace App\Exceptions;

use RuntimeException;

class PaymentLinkFailedException extends RuntimeException
{
    public static function providerUnreachable(): self
    {
        return new self('Payment service is unreachable. No booking was made. Please try again.');
    }
}
