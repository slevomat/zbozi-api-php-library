<?php

namespace SlevomatZboziApi\Request;

abstract class InvalidRequestException extends \Exception implements \SlevomatZboziApi\ZboziApiException
{

	/** @var string[] */
	private $messages;

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
	public function getMessages()
	{
		return $this->messages;
	}

}
