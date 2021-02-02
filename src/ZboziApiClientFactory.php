<?php declare(strict_types = 1);

namespace SlevomatZboziApi;

use SlevomatZboziApi\Request\RequestMakerFactory;
use SlevomatZboziApi\Response\ResponseValidator;
use SlevomatZboziApi\Type\TypeValidator;

class ZboziApiClientFactory
{

	/**
	 * @param string $partnerToken
	 * @param string $apiSecret
	 * @param string $apiUrl
	 * @param int $timeoutInSeconds (0 = unlimited)
	 * @param ZboziApiLogger $logger
	 * @return ZboziApiClient
	 */
	public static function create(
		string $partnerToken,
		string $apiSecret,
		string $apiUrl,
		int $timeoutInSeconds = 30,
		?ZboziApiLogger $logger = null
	): ZboziApiClient
	{
		TypeValidator::checkString($partnerToken);
		TypeValidator::checkString($apiSecret);
		TypeValidator::checkString($apiUrl);
		TypeValidator::checkInteger($timeoutInSeconds);

		$responseValidator = new ResponseValidator();
		$requestMaker = RequestMakerFactory::create($partnerToken, $apiSecret, $timeoutInSeconds, $logger);

		return new ZboziApiClient($requestMaker, $responseValidator, $apiUrl);
	}

}
