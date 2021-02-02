<?php declare(strict_types = 1);

namespace SlevomatZboziApi\Request;

class InvalidRequestType
{

	public const BAD_REQUEST = 1;
	public const INVALID_CREDENTIALS = 2;
	public const ORDER_NOT_FOUND = 3;
	public const ORDER_ITEM_NOT_FOUND = 4;
	public const INVALID_STATUS_CHANGE = 5;
	public const INVALID_CANCEL = 6;
	public const OTHER_ERROR = 7;
	public const ORDER_NOT_EXPORTED = 8;

}
