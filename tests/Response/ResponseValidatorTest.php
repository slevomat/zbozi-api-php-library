<?php declare(strict_types = 1);

namespace SlevomatZboziApi\Response;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use SlevomatZboziApi\Request\BadRequestException;
use SlevomatZboziApi\Request\InvalidCancelException;
use SlevomatZboziApi\Request\InvalidCredentialsException;
use SlevomatZboziApi\Request\InvalidRequestType;
use SlevomatZboziApi\Request\InvalidStatusChangeException;
use SlevomatZboziApi\Request\OrderItemNotFoundException;
use SlevomatZboziApi\Request\OrderNotFoundException;
use SlevomatZboziApi\Request\OtherRequestErrorException;

class ResponseValidatorTest extends TestCase
{

	private ResponseValidator $responseValidator;

	protected function setUp(): void
	{
		$this->responseValidator = new ResponseValidator();
	}

	public function testValidateResponseThrowsBadRequestException(): void
	{
		$this->expectException(BadRequestException::class);
		$this->expectExceptionMessage('Row 1, missing amount key. | Row 1, missing slevomatId key.');
		$response = $this->createClientErrorResponse(400, InvalidRequestType::BAD_REQUEST, [
			'Row 1, missing amount key.',
			'Row 1, missing slevomatId key.',
		]);
		$this->responseValidator->validateResponse($response);
	}

	public function testValidateResponseThrowsInvalidCredentialsException(): void
	{
		$this->expectException(InvalidCredentialsException::class);
		$this->expectExceptionMessage('some error');
		$response = $this->createClientErrorResponse(403, InvalidRequestType::INVALID_CREDENTIALS);
		$this->responseValidator->validateResponse($response);
	}

	public function testValidateResponseThrowsOrderNotFoundException(): void
	{
		$this->expectException(OrderNotFoundException::class);
		$this->expectExceptionMessage('some error');
		$response = $this->createClientErrorResponse(404, InvalidRequestType::ORDER_NOT_FOUND);
		$this->responseValidator->validateResponse($response);
	}

	public function testValidateResponseThrowsOrderItemNotFoundException(): void
	{
		$this->expectException(OrderItemNotFoundException::class);
		$this->expectExceptionMessage('some error');
		$response = $this->createClientErrorResponse(404, InvalidRequestType::ORDER_ITEM_NOT_FOUND);
		$this->responseValidator->validateResponse($response);
	}

	public function testValidateResponseThrowsInvalidStatusChangeException(): void
	{
		$this->expectException(InvalidStatusChangeException::class);
		$this->expectExceptionMessage('some error');
		$response = $this->createClientErrorResponse(422, InvalidRequestType::INVALID_STATUS_CHANGE);
		$this->responseValidator->validateResponse($response);
	}

	public function testValidateResponseThrowsInvalidCancelException(): void
	{
		$this->expectException(InvalidCancelException::class);
		$this->expectExceptionMessage('some error');
		$response = $this->createClientErrorResponse(422, InvalidRequestType::INVALID_CANCEL);
		$this->responseValidator->validateResponse($response);
	}

	public function testValidateResponseThrowsOtherErrorException(): void
	{
		$this->expectException(OtherRequestErrorException::class);
		$this->expectExceptionMessage('some error');
		$response = $this->createClientErrorResponse(422, InvalidRequestType::OTHER_ERROR);
		$this->responseValidator->validateResponse($response);
	}

	public function testValidateResponseThrowsResponseErrorExceptionForUnexpectedClientErrorStatusCode(): void
	{
		$this->expectException(ResponseErrorException::class);
		$this->expectExceptionMessage('Slevomat API 422 response contains unknown status 700000.');
		$response = $this->createClientErrorResponse(422, 700000);
		$this->responseValidator->validateResponse($response);
	}

	public function testValidateResponseThrowsResponseErrorExceptionForUnexpectedHttpStatus(): void
	{
		$this->expectException(ResponseErrorException::class);
		$this->expectExceptionMessage('Slevomat API responded with unexpected HTTP status code: 300.');
		$response = new ZboziApiResponse(300);
		$this->responseValidator->validateResponse($response);
	}

	public function testGetExpectedDeliveryDateReturnsDateTime(): void
	{
		$response = new ZboziApiResponse(200, [
			'expectedDeliveryDate' => '2012-01-01',
		]);
		$date = $this->responseValidator->getExpectedDeliveryDate($response);
		Assert::assertSame('2012-01-01', $date->format('Y-m-d'));
	}

	public function testGetExpectedDeliveryDateThrowsResponseExceptionForResponsesWithMissingExpectedDeliveryDateKey(): void
	{
		$this->expectException(ResponseErrorException::class);
		$this->expectExceptionMessage('Slevomat API response doesn\'t contain expectedDeliveryDate.');
		$response = new ZboziApiResponse(200, []);
		$this->responseValidator->getExpectedDeliveryDate($response);
	}

	public function testGetExpectedDeliveryDateThrowsResponseExceptionForResponsesWithInvalidExpectedDeliveryDateKey(): void
	{
		$this->expectException(ResponseErrorException::class);
		$this->expectExceptionMessage('Slevomat API invalid response: invalid expectedDeliveryDate nonsense.');
		$response = new ZboziApiResponse(200, [
			'expectedDeliveryDate' => 'nonsense',
		]);
		$this->responseValidator->getExpectedDeliveryDate($response);
	}

	public function testValidateResponseThrowsResponseErrorExceptionsWhenStatusKeyIsMissing(): void
	{
		$this->expectException(ResponseErrorException::class);
		$this->expectExceptionMessage('Slevomat API invalid 400 response: missing status.');
		$response = new ZboziApiResponse(400, [
			'messages' => ['someError'],
		]);
		$this->responseValidator->validateResponse($response);
	}

	public function testValidateResponseThrowsResponseErrorExceptionsWhenMessagesKeyIsMissing(): void
	{
		$this->expectException(ResponseErrorException::class);
		$this->expectExceptionMessage('Slevomat API invalid 400 response: missing messages.');
		$response = new ZboziApiResponse(400, [
			'status' => InvalidRequestType::BAD_REQUEST,
		]);
		$this->responseValidator->validateResponse($response);
	}

	public function testValidateResponseConsidersAny200ResponseAsValid(): void
	{
		$this->responseValidator->validateResponse(new ZboziApiResponse(200));
		$this->responseValidator->validateResponse(new ZboziApiResponse(204));
		Assert::assertTrue(true);
	}

	/**
	 * @param int $httpStatusCode
	 * @param int $clientErrorStatus
	 * @param string[] $messages
	 * @return ZboziApiResponse
	 */
	private function createClientErrorResponse(int $httpStatusCode, int $clientErrorStatus, array $messages = ['some error']): ZboziApiResponse
	{
		return new ZboziApiResponse(
			$httpStatusCode,
			[
				'status' => $clientErrorStatus,
				'messages' => $messages,
			],
		);
	}

}
