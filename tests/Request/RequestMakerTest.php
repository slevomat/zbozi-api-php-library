<?php

namespace SlevomatZboziApi\Request;

class RequestMakerTest extends \PHPUnit_Framework_TestCase
{

	/** @var \GuzzleHttp\Client|\PHPUnit_Framework_MockObject_MockBuilder */
	private $httpClientMock;

	/** @var string */
	private $partnerToken = 'sfdsfsdfwerwers';

	/** @var string */
	private $apiSecret = 'qwrwerwerwerwewer';

	/** @var \SlevomatZboziApi\ZboziApiLogger|\PHPUnit_Framework_MockObject_MockBuilder */
	private $loggerMock;

	public function setup()
	{
		$this->httpClientMock = $this->getMockBuilder('GuzzleHttp\Client')
			->disableOriginalConstructor()
			->getMock();

		$this->loggerMock = $this->getMockBuilder('SlevomatZboziApi\ZboziApiLogger')
			->disableOriginalConstructor()
			->getMock();
	}

	public function testSendPostRequestReturnsZboziApiResponse()
	{
		$this->loggerMock->expects(self::once())->method('log');

		$requestUrl = 'someUrl';
		$requestBody = [
			'autoMarkDelivered' => true,
		];
		$headers = [
			RequestMaker::HEADER_API_SECRET => $this->apiSecret,
			RequestMaker::HEADER_PARTNER_TOKEN => $this->partnerToken,
		];
		$request = new \GuzzleHttp\Message\Request('POST', $requestUrl, $headers);

		$responseData = [
			'expectedDeliveryDate' => '2012-01-01',
		];

		$this->httpClientMock
			->expects(self::once())
			->method('createRequest')
			->with('POST', $requestUrl)
			->willReturn($request);

		$this->httpClientMock
			->expects(self::once())
			->method('send')
			->willReturn($this->createFutureResponse(200, json_encode($responseData)));

		$requestMaker = $this->createRequestMaker();
		$response = $requestMaker->sendPostRequest($requestUrl, $requestBody);

		$this->assertInstanceOf('SlevomatZboziApi\Response\ZboziApiResponse', $response);
		$this->assertSame(200, $response->getStatusCode());
		$this->assertSame($responseData, $response->getBody());
	}

	public function testSendPostRequestReturnsZboziApiResponseAndIgnoresResponseBodyForResponsesWithStatusCodeStartingAt5()
	{
		$this->loggerMock->expects(self::once())->method('log');

		$this->httpClientMock->method('createRequest')->willReturn(new \GuzzleHttp\Message\Request('POST', 'someUrl'));
		$this->httpClientMock->method('send')->willReturn($this->createFutureResponse(500, '<html>Server error</html>'));

		$requestMaker = $this->createRequestMaker();
		$response = $requestMaker->sendPostRequest('somerUrl');

		$this->assertInstanceOf('SlevomatZboziApi\Response\ZboziApiResponse', $response);
		$this->assertSame(500, $response->getStatusCode());
		$this->assertSame(null, $response->getBody());
	}

	public function testZboziApiResponseIsReturnedForResponsesWithStatusCodeStartingAt4()
	{
		$this->loggerMock->expects(self::once())->method('log');

		$responseBody = '{"status":3,"messages":["OrderId #45445 not found."]}';
		$this->httpClientMock->method('createRequest')->willReturn(new \GuzzleHttp\Message\Request('POST', 'someUrl'));
		$this->httpClientMock->method('send')->willReturn($this->createFutureResponse(404, $responseBody));

		$requestMaker = $this->createRequestMaker();
		$response = $requestMaker->sendPostRequest('somerUrl');

		$this->assertInstanceOf('SlevomatZboziApi\Response\ZboziApiResponse', $response);
		$this->assertSame(404, $response->getStatusCode());
		$this->assertSame($responseBody, json_encode($response->getBody()));
	}

	/**
	 * @expectedException \SlevomatZboziApi\Request\ConnectionErrorException
	 * @expectedExceptionMessage Connection to Slevomat API failed.
	 */
	public function testConnectionErrorExceptionIsThrownWhenRequestExceptionDoesntContainResponse()
	{
		$this->loggerMock->expects(self::once())->method('log');

		$request = new \GuzzleHttp\Message\Request('POST', 'someUrl');
		$this->httpClientMock->method('createRequest')->willReturn($request);
		$this->httpClientMock->method('send')->willReturn($this->createFuture(function () use ($request) {
			throw new \GuzzleHttp\Exception\RequestException('some message', $request);
		}));

		$requestMaker = $this->createRequestMaker();
		$requestMaker->sendPostRequest('somerUrl');
	}

	public function testZboziApiResponseIsReturnedWhenRequestExceptionContainsResponse()
	{
		$response = new \GuzzleHttp\Message\Response(300);
		$this->loggerMock->expects(self::once())->method('log');

		$request = new \GuzzleHttp\Message\Request('POST', 'someUrl');
		$this->httpClientMock->method('createRequest')->willReturn($request);
		$this->httpClientMock->method('send')->willReturn($this->createFuture(function () use ($request, $response) {
			throw new \GuzzleHttp\Exception\RequestException('some message', $request, $response);
		}));

		$requestMaker = $this->createRequestMaker();
		$response = $requestMaker->sendPostRequest('somerUrl');
		$this->assertInstanceOf('SlevomatZboziApi\Response\ZboziApiResponse', $response);
	}

	/**
	 * @expectedException \SlevomatZboziApi\Response\ResponseErrorException
	 * @expectedExceptionMessage Slevomat API invalid response: invalid JSON data.
	 */
	public function testResponseErrorExceptionIsThrownForResponseWithInvalidJsonData()
	{
		$this->loggerMock->expects(self::once())->method('log');

		$this->httpClientMock->method('createRequest')->willReturn(new \GuzzleHttp\Message\Request('POST', 'someUrl'));
		$this->httpClientMock->method('send')->willReturn($this->createFutureResponse(200, '{"someData":xxx}'));

		$requestMaker = $this->createRequestMaker();
		$requestMaker->sendPostRequest('somerUrl');
	}

	public function testSendPostRequestWorksWithoutLogger()
	{
		$this->loggerMock = null;

		$this->httpClientMock->method('createRequest')->willReturn(new \GuzzleHttp\Message\Request('POST', 'someUrl'));
		$this->httpClientMock->method('send')->willReturn($this->createFutureResponse(200, '{"someData":8}'));

		$requestMaker = $this->createRequestMaker();
		$requestMaker->sendPostRequest('somerUrl');
		$this->assertTrue(true);
	}

	/**
	 * @return \SlevomatZboziApi\Request\RequestMaker
	 */
	private function createRequestMaker()
	{
		return new RequestMaker($this->httpClientMock, $this->partnerToken, $this->apiSecret, 30, $this->loggerMock);
	}

	/**
	 * @param callable $wait
	 * @param callable|null $cancel
	 * @return \GuzzleHttp\Message\FutureResponse
	 */
	private function createFuture(callable $wait, callable $cancel = null)
	{
		$deferred = new \React\Promise\Deferred();

		return new \GuzzleHttp\Message\FutureResponse(
			$deferred->promise(),
			function () use ($deferred, $wait) {
				$deferred->resolve($wait());
			},
			$cancel
		);
	}

	/**
	 * @param integer $httpStatusCode
	 * @param string $body
	 * @return \GuzzleHttp\Message\FutureResponse
	 */
	private function createFutureResponse($httpStatusCode, $body)
	{
		$response = new \GuzzleHttp\Message\Response(
			$httpStatusCode,
			[],
			\GuzzleHttp\Stream\Stream::factory($body)
		);

		return $this->createFuture(function () use ($response) {
			return $response;
		});
	}

}
