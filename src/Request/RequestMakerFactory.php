<?php

namespace SlevomatZboziApi\Request;

use SlevomatZboziApi\Type\TypeValidator;
use SlevomatZboziApi\ZboziApiLogger;

class RequestMakerFactory
{

	/**
	 * @param string $partnerToken
	 * @param string $apiSecret
	 * @param integer $timeoutInSeconds
	 * @param \SlevomatZboziApi\ZboziApiLogger $logger
	 * @return \SlevomatZboziApi\Request\RequestMaker
	 */
	public static function create($partnerToken, $apiSecret, $timeoutInSeconds, ZboziApiLogger $logger = null)
	{
		TypeValidator::checkString($partnerToken);
		TypeValidator::checkString($apiSecret);
		TypeValidator::checkInteger($timeoutInSeconds);

		$client = new \GuzzleHttp\Client();

		return new RequestMaker($client, $partnerToken, $apiSecret, $timeoutInSeconds, $logger);
	}

}
