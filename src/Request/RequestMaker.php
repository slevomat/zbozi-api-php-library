<?php declare(strict_types = 1);

namespace SlevomatZboziApi\Request;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SlevomatZboziApi\Response\ResponseErrorException;
use SlevomatZboziApi\Response\ResponseParsingErrorException;
use SlevomatZboziApi\Response\ZboziApiResponse;
use SlevomatZboziApi\Type\TypeValidator;
use SlevomatZboziApi\ZboziApiLogger;
use function GuzzleHttp\Psr7\stream_for;
use function json_decode;
use function json_encode;
use function json_last_error;
use function preg_match;
use function sprintf;
use const JSON_ERROR_NONE;
use const PHP_VERSION;

class RequestMaker
{

	public const HEADER_PARTNER_TOKEN = 'X-PartnerToken';
	public const HEADER_API_SECRET = 'X-ApiSecret';
	public const HEADER_USER_AGENT = 'User-Agent';

	private ClientInterface $client;

	private string $partnerToken;

	private string $apiSecret;

	private int $timeoutInSeconds;

	private ?ZboziApiLogger $logger = null;

	public function __construct(
		ClientInterface $client,
		string $partnerToken,
		string $apiSecret,
		int $timeoutInSeconds,
		?ZboziApiLogger $logger = null
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
	 * @return ZboziApiResponse
	 */
	public function sendPostRequest(string $url, ?array $body = null): ZboziApiResponse
	{
		TypeValidator::checkString($url);

		$request = new Request(
			'POST',
			$url,
			[
				self::HEADER_PARTNER_TOKEN => $this->partnerToken,
				self::HEADER_API_SECRET => $this->apiSecret,
				self::HEADER_USER_AGENT => sprintf('SlevomatZboziApiClient/PHP %s', PHP_VERSION),
			],
			$body === null ? null : stream_for(json_encode($body)),
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
			} catch (RequestException $e) {
				$response = $e->getResponse();
				$this->log($request, $response);
				if ($response !== null) {
					return $this->getZboziApiResponse($response);
				}

				throw new ConnectionErrorException('Connection to Slevomat API failed.', $e->getCode(), $e);
			}

		} catch (ResponseParsingErrorException $e) {
			$this->log($request, $response ?? null, true);

			throw new ResponseErrorException('Slevomat API invalid response: invalid JSON data.', $e->getCode(), $e);
		}
	}

	private function getZboziApiRequest(RequestInterface $request): ZboziApiRequest
	{
		$body = (string) $request->getBody();

		return new ZboziApiRequest(
			$request->getMethod(),
			(string) $request->getUri(),
			$request->getHeaders(),
			$body === '' ? null : json_decode($body, true),
		);
	}

	private function getZboziApiResponse(ResponseInterface $response, bool $ignoreBody = false): ZboziApiResponse
	{
		if (!$ignoreBody && preg_match('~^[2|4]~', (string) $response->getStatusCode()) === 1) {
			$bodyEncoded = (string) $response->getBody();
			$body = $bodyEncoded === '' ? null : json_decode($bodyEncoded, true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				throw new ResponseParsingErrorException();
			}

			return new ZboziApiResponse($response->getStatusCode(), $body);
		}

		return new ZboziApiResponse($response->getStatusCode());
	}

	private function log(
		RequestInterface $request,
		?ResponseInterface $response = null,
		bool $ignoreBody = false
	): void
	{
		if ($this->logger === null) {
			return;
		}

		$this->logger->log(
			$this->getZboziApiRequest($request),
			$response === null ? null : $this->getZboziApiResponse($response, $ignoreBody),
		);
	}

}
