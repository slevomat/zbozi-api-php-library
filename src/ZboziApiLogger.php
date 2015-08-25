<?php

namespace SlevomatZboziApi;

use SlevomatZboziApi\Request\ZboziApiRequest;
use SlevomatZboziApi\Response\ZboziApiResponse;

interface ZboziApiLogger
{

	public function log(ZboziApiRequest $request, ZboziApiResponse $response = null);

}
