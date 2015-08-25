<?php

namespace SlevomatZboziApi\Request;

use SlevomatZboziApi\Type\TypeValidator;

class ZboziApiRequest
{

	/** @var string */
	private $method;

	/** @var string */
	private $url;

	/** @var mixed[] */
	private $headers;

	/** @var mixed[]|null */
	private $body;

	/**
	 * @param string $method
	 * @param string $url
	 * @param array $headers
	 * @param array|null $body
	 */
	public function __construct($method, $url, array $headers, $body = null)
	{
		TypeValidator::checkString($method);
		TypeValidator::checkString($url);

		$this->method = $method;
		$this->url = $url;
		$this->headers = $headers;
		$this->body = $body;
	}

	/**
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @return \mixed[]
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * @param string $headerName
	 * @return mixed|null
	 */
	public function getHeader($headerName)
	{
		TypeValidator::checkString($headerName);

		if (isset($this->headers[$headerName])) {
			return is_array($this->headers[$headerName]) ? $this->headers[$headerName][0] : $this->headers[$headerName];
		}

		return null;
	}

	/**
	 * @return \mixed[]|null
	 */
	public function getBody()
	{
		return $this->body;
	}

}
