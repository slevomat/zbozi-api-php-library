<?php

namespace SlevomatZboziApi\Request;

class OtherRequestErrorException extends \Exception implements \SlevomatZboziApi\Request\InvalidRequestException
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
