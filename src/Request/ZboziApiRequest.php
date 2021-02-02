<?php declare(strict_types = 1);

namespace SlevomatZboziApi\Request;

use SlevomatZboziApi\Type\TypeValidator;
use function is_array;

class ZboziApiRequest
{

	private string $method;

	private string $url;

	/** @var mixed[] */
	private array $headers;

	/** @var mixed[]|null */
	private ?array $body = null;

	/**
	 * @param string $method
	 * @param string $url
	 * @param mixed[] $headers
	 * @param mixed[]|null $body
	 */
	public function __construct(string $method, string $url, array $headers, ?array $body = null)
	{
		TypeValidator::checkString($method);
		TypeValidator::checkString($url);

		$this->method = $method;
		$this->url = $url;
		$this->headers = $headers;
		$this->body = $body;
	}

	public function getMethod(): string
	{
		return $this->method;
	}

	public function getUrl(): string
	{
		return $this->url;
	}

	/**
	 * @return mixed[]
	 */
	public function getHeaders(): array
	{
		return $this->headers;
	}

	/**
	 * @param string $headerName
	 * @return mixed|null
	 */
	public function getHeader(string $headerName)
	{
		TypeValidator::checkString($headerName);

		if (isset($this->headers[$headerName])) {
			return is_array($this->headers[$headerName]) ? $this->headers[$headerName][0] : $this->headers[$headerName];
		}

		return null;
	}

	/**
	 * @return mixed[]|null
	 */
	public function getBody(): ?array
	{
		return $this->body;
	}

}
