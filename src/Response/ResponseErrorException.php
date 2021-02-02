<?php declare(strict_types = 1);

namespace SlevomatZboziApi\Response;

use Exception;
use SlevomatZboziApi\ZboziApiException;

class ResponseErrorException extends Exception implements ZboziApiException
{

}
