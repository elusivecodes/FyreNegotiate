<?php
declare(strict_types=1);

namespace Fyre\Http\Exceptions;

use
    RunTimeException;

/**
 * NegotiateException
 */
class NegotiateException extends RunTimeException
{

    public static function forNoSupportedValues()
    {
        return new static('No supported values supplied');
    }

}
