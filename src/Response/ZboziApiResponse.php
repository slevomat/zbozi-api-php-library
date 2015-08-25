<?php

namespace SlevomatZboziApi\Response;

use SlevomatZboziApi\Type\TypeValidator;

class ZboziApiResponse
{

	/** @var array|null */
	private $body;

	/** @var integer */
	private $statusCode;

	/**
	 * @param integer $statusCode
	 * @param array|null $body
	 */
	public function __construct($statusCode, array $body = null)
	{
		TypeValidator::checkInteger($statusCode);

		$this->body = $body;
		$this->statusCode = $statusCode;
	}

	/**
	 * @return array|null
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * @return integer
	 */
	public function getStatusCode()
	{
		return $this->statusCode;
	}

}
