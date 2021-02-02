<?php declare(strict_types = 1);

namespace SlevomatZboziApi\Request;

use Exception;
use SlevomatZboziApi\ZboziApiException;

class ConnectionErrorException extends Exception implements ZboziApiException
{

}
