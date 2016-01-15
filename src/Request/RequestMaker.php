<?php

namespace SlevomatZboziApi\Request;

use SlevomatZboziApi\Response\ZboziApiResponse;
use SlevomatZboziApi\Type\TypeValidator;
use SlevomatZboziApi\ZboziApiLogger;

class RequestMaker
{

	const HEADER_PARTNER_TOKEN = 'X-PartnerToken';
	const HEADER_API_SECRET = 'X-ApiSecret';
	const HEADER_USER_AGENT = 'User-Agent';

	/** @var \GuzzleHttp\ClientInterface */
	private $client;

	/** @var string */
	private $partnerToken;

	/** @var string */
	private $apiSecret;

	/** @var integer */
	private $timeoutInSeconds;

	/** @var \SlevomatZboziApi\ZboziApiLogger|null */
	private $logger;

	/**
	 * @param \GuzzleHttp\ClientInterface $client
	 * @param string $partnerToken
	 * @param string $apiSecret
	 * @param integer $timeoutInSeconds
	 * @param \SlevomatZboziApi\ZboziApiLogger $logger
	 */
	public function __construct(
		\GuzzleHttp\ClientInterface $client,
		$partnerToken,
		$apiSecret,
		$timeoutInSeconds,
		ZboziApiLogger $logger = null
	)
	{
		TypeValidator::checkString($partnerToken);
		TypeValidator::checkString($apiSecret);
		TypeValidator::checkInteger($timeoutInSeconds);

		$this->client = $client;
		$this->partnerToken = $partnerToken;
		$this->apiSecret = $apiSecret;
		$this->timeoutInSeconds = $timeoutInSeconds;
		$this->logger = $logger;
	}

	/**
	 * @param string $url
	 * @param mixed[]|null $body
	 * @return \SlevomatZboziApi\Response\ZboziApiResponse
	 */
	public function sendPostRequest($url, array $body = null)
	{
		TypeValidator::checkString($url);

		$request = new \GuzzleHttp\Psr7\Request(
			'POST',
			$url,
			[
				static::HEADER_PARTNER_TOKEN => $this->partnerToken,
				static::HEADER_API_SECRET => $this->apiSecret,
				static::HEADER_USER_AGENT => sprintf('SlevomatZboziApiClient/PHP %s', PHP_VERSION),
			],
			$body === null ? null : \GuzzleHttp\Psr7\stream_for(json_encode($body))
		);

		$options = [
			'allow_redirects' => false,
			'verify' => true,
			'decode_content' => true,
			'expect' => false,
			'timeout' => $this->timeoutInSeconds,
		];
		try {
			try {
				$response = $this->client->send($request, $options);
				$this->log($request, $response);

				return $this->getZboziApiResponse($response);

			} catch (\GuzzleHttp\Exception\RequestException $e) {
				$response = $e->getResponse();
				$this->log($request, $response);
				if ($response !== null) {
					return $this->getZboziApiResponse($response);
				}
				throw new \SlevomatZboziApi\Request\ConnectionErrorException('Connection to Slevomat API failed.', $e->getCode(), $e);
			}

		} catch (\SlevomatZboziApi\Response\ResponseParsingErrorException $e) {
			$this->log($request, isset($response) ? $response : null, true);
			throw new \SlevomatZboziApi\Response\ResponseErrorException('Slevomat API invalid response: invalid JSON data.', $e->getCode(), $e);
		}
	}

	/**
	 * @param \Psr\Http\Message\RequestInterface $request
	 * @return \SlevomatZboziApi\Request\ZboziApiRequest
	 */
	private function getZboziApiRequest(\Psr\Http\Message\RequestInterface $request)
	{
		$body = (string) $request->getBody();

		return new ZboziApiRequest(
			$request->getMethod(),
			(string) $request->getUri(),
			$request->getHeaders(),
			$body === '' ? null : json_decode($body, true)
		);
	}

	/**
	 * @param \Psr\Http\Message\ResponseInterface $response
	 * @param boolean $ignoreBody
	 * @return \SlevomatZboziApi\Response\ZboziApiResponse
	 */
	private function getZboziApiResponse(\Psr\Http\Message\ResponseInterface $response, $ignoreBody = false)
	{
		if (!$ignoreBody && preg_match('~^[2|4]~', $response->getStatusCode())) {
			$bodyEncoded = (string) $response->getBody();
			$body = $bodyEncoded === '' ? null : json_decode($bodyEncoded, true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				throw new \SlevomatZboziApi\Response\ResponseParsingErrorException();
			}
			return new ZboziApiResponse($response->getStatusCode(), $body);

		} else {
			return new ZboziApiResponse($response->getStatusCode());
		}
	}

	/**
	 * @param \Psr\Http\Message\RequestInterface $request
	 * @param \Psr\Http\Message\ResponseInterface|null $response
	 * @param boolean $ignoreBody
	 */
	private function log(
		\Psr\Http\Message\RequestInterface $request,
		\Psr\Http\Message\ResponseInterface $response = null,
		$ignoreBody = false
	)
	{
		if ($this->logger === null) {
			return;
		}

		$this->logger->log(
			$this->getZboziApiRequest($request),
			$response === null ? null : $this->getZboziApiResponse($response, $ignoreBody)
		);
	}

}
