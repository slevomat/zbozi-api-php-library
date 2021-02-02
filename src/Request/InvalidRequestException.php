<?php declare(strict_types = 1);

namespace SlevomatZboziApi\Request;

use Exception;
use SlevomatZboziApi\ZboziApiException;
use function implode;

abstract class InvalidRequestException extends Exception implements ZboziApiException
{

	/** @var string[] */
	private array $messages;

	/**
	 * @param string[] $messages
	 */
	public function __construct(array $messages)
	{
		$this->messages = $messages;

		parent::__construct(implode(' | ', $messages));
	}

	/**
	 * @return string[]
	 */
	public function getMessages(): array
	{
		return $this->messages;
	}

}
