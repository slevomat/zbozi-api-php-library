<?php

class ExampleLogger implements \SlevomatZboziApi\ZboziApiLogger
{

	public function log(\SlevomatZboziApi\Request\ZboziApiRequest $request, \SlevomatZboziApi\Response\ZboziApiResponse $response = null)
	{
		$responseData = $response === null
			? '-'
			: sprintf('%s %s', $response->getStatusCode(), $response->getBody() === null ? '' : json_encode($response->getBody()));

		echo sprintf('<br><b>HTTP request</b>: %s %s | (%s:%s) | %s<br><br><b>HTTP response</b>: %s<br>',
			$request->getMethod(),
			$request->getUrl(),
			$request->getHeader(\SlevomatZboziApi\Request\RequestMaker::HEADER_PARTNER_TOKEN),
			$request->getHeader(\SlevomatZboziApi\Request\RequestMaker::HEADER_API_SECRET),
			$request->getBody() === null ? '-' : json_encode($request->getBody()),
			$responseData
		);
	}
}
