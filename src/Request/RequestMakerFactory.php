<?php declare(strict_types = 1);

namespace SlevomatZboziApi\Request;

use GuzzleHttp\Client;
use SlevomatZboziApi\Type\TypeValidator;
use SlevomatZboziApi\ZboziApiLogger;

class RequestMakerFactory
{

	public static function create(string $partnerToken, string $apiSecret, int $timeoutInSeconds, ?ZboziApiLogger $logger = null): RequestMaker
	{
		TypeValidator::checkString($partnerToken);
		TypeValidator::checkString($apiSecret);
		TypeValidator::checkInteger($timeoutInSeconds);

		$client = new Client();

		return new RequestMaker($client, $partnerToken, $apiSecret, $timeoutInSeconds, $logger);
	}

}
