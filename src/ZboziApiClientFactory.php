<?php

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
	 * @param integer $timeoutInSeconds (0 = unlimited)
	 * @param \SlevomatZboziApi\ZboziApiLogger $logger
	 * @return \SlevomatZboziApi\ZboziApiClient
	 */
	public static function create($partnerToken, $apiSecret, $apiUrl, $timeoutInSeconds = 30, ZboziApiLogger $logger = null)
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
