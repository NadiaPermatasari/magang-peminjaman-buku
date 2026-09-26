<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A business-rule violation in the loan/fine flow (e.g. member ineligible,
 * no copy available, illegal status transition). Controllers catch this and
 * flash the message back to the user instead of a generic 500.
 */
class LoanException extends RuntimeException
{
    //
}
