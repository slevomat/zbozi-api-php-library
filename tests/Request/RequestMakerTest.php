<?php declare(strict_types = 1);

namespace SlevomatZboziApi\Request;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SlevomatZboziApi\Response\ResponseErrorException;
use SlevomatZboziApi\Response\ZboziApiResponse;
use SlevomatZboziApi\ZboziApiLogger;
use function json_decode;
use function json_encode;

class RequestMakerTest extends TestCase
{

	/** @var Client|MockObject */
	private $httpClientMock;

	private string $partnerToken = 'sfdsfsdfwerwers';

	private string $apiSecret = 'qwrwerwerwerwewer';

	/** @var ZboziApiLogger|MockObject */
	private $loggerMock;

	protected function setUp(): void
	{
		$this->httpClientMock = $this->getMockBuilder('GuzzleHttp\Client')
			->disableOriginalConstructor()
			->getMock();

		$this->loggerMock = $this->getMockBuilder('SlevomatZboziApi\ZboziApiLogger')
			->disableOriginalConstructor()
			->getMock();
	}

	public function testSendPostRequestReturnsZboziApiResponse(): void
	{
		$requestUrl = 'someUrl';
		$requestBody = [
			'autoMarkDelivered' => true,
		];
		$responseBody = [
			'expectedDeliveryDate' => '2012-01-01',
		];
		$encodedBody = json_encode($responseBody);
		if ($encodedBody === false) {
			throw new Exception('JSON encode failed');
		}
		$response = new Response(200, [], $encodedBody);

		$this->httpClientMock
			->expects(self::once())
			->method('send')
			->with(Assert::callback(static function (Request $request) use ($requestBody): bool {
				$body = json_decode((string) $request->getBody(), true);

				return $request->getMethod() === 'POST' && $request->getUri()->getPath() === 'someUrl' && $body === $requestBody;
			}))
			->willReturn($response);

		$this->loggerMock
			->expects(self::once())
			->method('log')
			->with(
				Assert::callback(static fn (ZboziApiRequest $request) => $request->getMethod() === 'POST' && $request->getUrl() === 'someUrl' && $request->getBody() === $requestBody),
				Assert::callback(static fn (ZboziApiResponse $response) => $response->getStatusCode() === 200 && $response->getBody() === $responseBody),
			);

		$requestMaker = $this->createRequestMaker();
		$response = $requestMaker->sendPostRequest($requestUrl, $requestBody);
		Assert::assertSame(200, $response->getStatusCode());
		Assert::assertSame($responseBody, $response->getBody());
	}

	public function testSendPostRequestReturnsZboziApiResponseAndIgnoresResponseBodyForResponsesWithStatusCodeStartingAt5(): void
	{
		$this->loggerMock->expects(self::once())->method('log');

		$this->httpClientMock
			->expects(self::once())
			->method('send')
			->willReturn(new Response(500, [], '<html>Server error</html>'));

		$requestMaker = $this->createRequestMaker();
		$response = $requestMaker->sendPostRequest('somerUrl');

		Assert::assertSame(500, $response->getStatusCode());
		Assert::assertNull($response->getBody());
	}

	public function testZboziApiResponseIsReturnedForResponsesWithStatusCodeStartingAt4(): void
	{
		$this->loggerMock->expects(self::once())->method('log');

		$responseBody = '{"status":3,"messages":["OrderId #45445 not found."]}';
		$this->httpClientMock
			->expects(self::once())
			->method('send')
			->willReturn(new Response(404, [], $responseBody));

		$requestMaker = $this->createRequestMaker();
		$response = $requestMaker->sendPostRequest('somerUrl');

		Assert::assertSame(404, $response->getStatusCode());
		Assert::assertSame($responseBody, json_encode($response->getBody()));
	}

	public function testConnectionErrorExceptionIsThrownWhenRequestExceptionDoesntContainResponse(): void
	{
		$this->expectException(ConnectionErrorException::class);
		$this->expectExceptionMessage('Connection to Slevomat API failed.');
		$this->loggerMock->expects(self::once())->method('log');

		$request = new Request('POST', 'someUrl');
		$this->httpClientMock
			->expects(self::once())
			->method('send')
			->willThrowException(new RequestException('some message', $request));

		$requestMaker = $this->createRequestMaker();
		$requestMaker->sendPostRequest('somerUrl');
	}

	public function testZboziApiResponseIsReturnedWhenRequestExceptionContainsResponse(): void
	{
		$response = new Response(300);
		$this->loggerMock->expects(self::once())->method('log');

		$request = new Request('POST', 'someUrl');
		$this->httpClientMock
			->expects(self::once())
			->method('send')
			->willThrowException(new RequestException('some message', $request, $response));

		$requestMaker = $this->createRequestMaker();
		$requestMaker->sendPostRequest('someUrl');
	}

	public function testResponseErrorExceptionIsThrownForResponseWithInvalidJsonData(): void
	{
		$this->expectException(ResponseErrorException::class);
		$this->expectExceptionMessage('Slevomat API invalid response: invalid JSON data.');
		$this->loggerMock->expects(self::once())->method('log');

		$this->httpClientMock
			->expects(self::once())
			->method('send')
			->willReturn(new Response(200, [], '{"someData":xxx}'));

		$requestMaker = $this->createRequestMaker();
		$requestMaker->sendPostRequest('someUrl');
	}

	public function testSendPostRequestWorksWithoutLogger(): void
	{
		$this->httpClientMock
			->expects(self::once())
			->method('send')
			->with(Assert::callback(static fn (Request $subject) => $subject->getMethod() === 'POST' && $subject->getUri()->getPath() === 'someUrl'))
			->willReturn(new Response(200, [], '{"someData":8}'));

		$requestMaker = $this->createRequestMaker(true);
		$requestMaker->sendPostRequest('someUrl');
		Assert::assertTrue(true);
	}

	private function createRequestMaker(bool $excludeLogger = false): RequestMaker
	{
		$logger = $excludeLogger ? null : $this->loggerMock;

		return new RequestMaker($this->httpClientMock, $this->partnerToken, $this->apiSecret, 30, $logger);
	}

}
