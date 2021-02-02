<?php declare(strict_types = 1);

namespace SlevomatZboziApi\Type;

use Exception;
use SlevomatZboziApi\ZboziApiException;

class TypeValidationFailedException extends Exception implements ZboziApiException
{

}
