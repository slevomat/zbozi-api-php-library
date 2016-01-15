<?php

namespace SlevomatZboziApi\Request;

use SlevomatZboziApi\Response\ZboziApiResponse;

class RequestMakerTest extends \PHPUnit_Framework_TestCase
{

	/** @var \GuzzleHttp\Client|\PHPUnit_Framework_MockObject_MockObject */
	private $httpClientMock;

	/** @var string */
	private $partnerToken = 'sfdsfsdfwerwers';

	/** @var string */
	private $apiSecret = 'qwrwerwerwerwewer';

	/** @var \SlevomatZboziApi\ZboziApiLogger|\PHPUnit_Framework_MockObject_MockObject */
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
		$requestUrl = 'someUrl';
		$requestBody = [
			'autoMarkDelivered' => true,
		];
		$responseBody = [
			'expectedDeliveryDate' => '2012-01-01',
		];
		$response = new \GuzzleHttp\Psr7\Response(200, [], json_encode($responseBody));

		$this->httpClientMock
			->expects(self::once())
			->method('send')
			->with($this->callback(function (\GuzzleHttp\Psr7\Request $request) use ($requestBody) {
				$body = json_decode((string) $request->getBody(), true);
				return $request->getMethod() === 'POST' && $request->getUri()->getPath() === 'someUrl' && $body === $requestBody;
			}))
			->willReturn($response);

		$this->loggerMock
			->expects(self::once())
			->method('log')
			->with(
				$this->callback(function (ZboziApiRequest $request) use ($requestBody) {
					return $request->getMethod() === 'POST' && $request->getUrl() === 'someUrl' && $request->getBody() === $requestBody;
				}),
				$this->callback(function (ZboziApiResponse $response) use ($responseBody) {
					return $response->getStatusCode() === 200 && $response->getBody() === $responseBody;
				})
			);

		$requestMaker = $this->createRequestMaker();
		$response = $requestMaker->sendPostRequest($requestUrl, $requestBody);
		$this->assertInstanceOf('SlevomatZboziApi\Response\ZboziApiResponse', $response);
		$this->assertSame(200, $response->getStatusCode());
		$this->assertSame($responseBody, $response->getBody());
	}

	public function testSendPostRequestReturnsZboziApiResponseAndIgnoresResponseBodyForResponsesWithStatusCodeStartingAt5()
	{
		$this->loggerMock->expects(self::once())->method('log');

		$this->httpClientMock
			->expects(self::once())
			->method('send')
			->willReturn(new \GuzzleHttp\Psr7\Response(500, [], '<html>Server error</html>'));

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
		$this->httpClientMock
			->expects(self::once())
			->method('send')
			->willReturn(new \GuzzleHttp\Psr7\Response(404, [], $responseBody));

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

		$request = new \GuzzleHttp\Psr7\Request('POST', 'someUrl');
		$this->httpClientMock
			->expects(self::once())
			->method('send')
			->willThrowException(new \GuzzleHttp\Exception\RequestException('some message', $request));

		$requestMaker = $this->createRequestMaker();
		$requestMaker->sendPostRequest('somerUrl');
	}

	public function testZboziApiResponseIsReturnedWhenRequestExceptionContainsResponse()
	{
		$response = new \GuzzleHttp\Psr7\Response(300);
		$this->loggerMock->expects(self::once())->method('log');

		$request = new \GuzzleHttp\Psr7\Request('POST', 'someUrl');
		$this->httpClientMock
			->expects(self::once())
			->method('send')
			->willThrowException(new \GuzzleHttp\Exception\RequestException('some message', $request, $response));

		$requestMaker = $this->createRequestMaker();
		$response = $requestMaker->sendPostRequest('someUrl');
		$this->assertInstanceOf('SlevomatZboziApi\Response\ZboziApiResponse', $response);
	}

	/**
	 * @expectedException \SlevomatZboziApi\Response\ResponseErrorException
	 * @expectedExceptionMessage Slevomat API invalid response: invalid JSON data.
	 */
	public function testResponseErrorExceptionIsThrownForResponseWithInvalidJsonData()
	{
		$this->loggerMock->expects(self::once())->method('log');

		$this->httpClientMock
			->expects(self::once())
			->method('send')
			->willReturn(new \GuzzleHttp\Psr7\Response(200, [], '{"someData":xxx}'));

		$requestMaker = $this->createRequestMaker();
		$requestMaker->sendPostRequest('someUrl');
	}

	public function testSendPostRequestWorksWithoutLogger()
	{
		$this->loggerMock = null;

		$this->httpClientMock
			->expects(self::once())
			->method('send')
			->with($this->callback(function (\GuzzleHttp\Psr7\Request $subject) {
				return $subject->getMethod() === 'POST' && $subject->getUri()->getPath() === 'someUrl';
			}))
			->willReturn(new \GuzzleHttp\Psr7\Response(200, [], '{"someData":8}'));

		$requestMaker = $this->createRequestMaker();
		$requestMaker->sendPostRequest('someUrl');
		$this->assertTrue(true);
	}

	/**
	 * @return \SlevomatZboziApi\Request\RequestMaker
	 */
	private function createRequestMaker()
	{
		return new RequestMaker($this->httpClientMock, $this->partnerToken, $this->apiSecret, 30, $this->loggerMock);
	}

}
