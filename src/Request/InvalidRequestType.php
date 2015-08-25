<?php

namespace SlevomatZboziApi\Request;

class InvalidRequestType
{

	const BAD_REQUEST = 1;
	const INVALID_CREDENTIALS = 2;
	const ORDER_NOT_FOUND = 3;
	const ORDER_ITEM_NOT_FOUND = 4;
	const INVALID_STATUS_CHANGE = 5;
	const INVALID_CANCEL = 6;
	const OTHER_ERROR = 7;
	const ORDER_NOT_EXPORTED = 8;

}
