<?php

namespace SlevomatZboziApi\Request;

interface InvalidRequestException extends \SlevomatZboziApi\ZboziApiException
{

	/**
	 * @return string[]
	 */
	public function getMessages();

}
