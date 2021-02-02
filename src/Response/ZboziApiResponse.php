<?php declare(strict_types = 1);

namespace SlevomatZboziApi\Response;

use SlevomatZboziApi\Type\TypeValidator;

class ZboziApiResponse
{

	/** @var mixed[]|null */
	private ?array $body = null;

	private int $statusCode;

	/**
	 * @param int $statusCode
	 * @param mixed[]|null $body
	 */
	public function __construct(int $statusCode, ?array $body = null)
	{
		TypeValidator::checkInteger($statusCode);

		$this->body = $body;
		$this->statusCode = $statusCode;
	}

	/**
	 * @return mixed[]|null
	 */
	public function getBody(): ?array
	{
		return $this->body;
	}

	public function getStatusCode(): int
	{
		return $this->statusCode;
	}

}
