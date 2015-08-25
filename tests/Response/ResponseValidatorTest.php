<?php

namespace SlevomatZboziApi\Response;

use SlevomatZboziApi\Request\InvalidRequestType;

class ResponseValidatorTest extends \PHPUnit_Framework_TestCase
{

	/** @var \SlevomatZboziApi\Response\ResponseValidator|\PHPUnit_Framework_MockObject_MockBuilder */
	private $responseValidator;

	public function setup()
	{
		$this->responseValidator = new ResponseValidator();
	}

	/**
	 * @expectedException \SlevomatZboziApi\Request\BadRequestException
	 * @expectedExceptionMessage Row 1, missing amount key. | Row 1, missing slevomatId key.
	 */
	public function testValidateResponseThrowsBadRequestException()
	{
		$response = $this->createClientErrorResponse(400, InvalidRequestType::BAD_REQUEST, [
			'Row 1, missing amount key.',
			'Row 1, missing slevomatId key.'
		]);
		$this->responseValidator->validateResponse($response);
	}

	/**
	 * @expectedException \SlevomatZboziApi\Request\InvalidCredentialsException
	 * @expectedExceptionMessage some error
	 */
	public function testValidateResponseThrowsInvalidCredentialsException()
	{
		$response = $this->createClientErrorResponse(403, InvalidRequestType::INVALID_CREDENTIALS);
		$this->responseValidator->validateResponse($response);
	}

	/**
	 * @expectedException \SlevomatZboziApi\Request\OrderNotFoundException
	 * @expectedExceptionMessage some error
	 */
	public function testValidateResponseThrowsOrderNotFoundException()
	{
		$response = $this->createClientErrorResponse(404, InvalidRequestType::ORDER_NOT_FOUND);
		$this->responseValidator->validateResponse($response);
	}

	/**
	 * @expectedException \SlevomatZboziApi\Request\OrderItemNotFoundException
	 * @expectedExceptionMessage some error
	 */
	public function testValidateResponseThrowsOrderItemNotFoundException()
	{
		$response = $this->createClientErrorResponse(404, InvalidRequestType::ORDER_ITEM_NOT_FOUND);
		$this->responseValidator->validateResponse($response);
	}

	/**
	 * @expectedException \SlevomatZboziApi\Request\InvalidStatusChangeException
	 * @expectedExceptionMessage some error
	 */
	public function testValidateResponseThrowsInvalidStatusChangeException()
	{
		$response = $this->createClientErrorResponse(422, InvalidRequestType::INVALID_STATUS_CHANGE);
		$this->responseValidator->validateResponse($response);
	}

	/**
	 * @expectedException \SlevomatZboziApi\Request\InvalidCancelException
	 * @expectedExceptionMessage some error
	 */
	public function testValidateResponseThrowsInvalidCancelException()
	{
		$response = $this->createClientErrorResponse(422, InvalidRequestType::INVALID_CANCEL);
		$this->responseValidator->validateResponse($response);
	}

	/**
	 * @expectedException \SlevomatZboziApi\Request\OtherRequestErrorException
	 * @expectedExceptionMessage some error
	 */
	public function testValidateResponseThrowsOtherErrorException()
	{
		$response = $this->createClientErrorResponse(422, InvalidRequestType::OTHER_ERROR);
		$this->responseValidator->validateResponse($response);
	}

	/**
	 * @expectedException \SlevomatZboziApi\Response\ResponseErrorException
	 * @expectedExceptionMessage Slevomat API 422 response contains unknown status 700000.
	 */
	public function testValidateResponseThrowsResponseErrorExceptionForUnexpectedClientErrorStatusCode()
	{
		$response = $this->createClientErrorResponse(422, 700000);
		$this->responseValidator->validateResponse($response);
	}

	/**
	 * @expectedException \SlevomatZboziApi\Response\ResponseErrorException
	 * @expectedExceptionMessage Slevomat API responded with unexpected HTTP status code: 300.
	 */
	public function testValidateResponseThrowsResponseErrorExceptionForUnexpectedHttpStatus()
	{
		$response = new ZboziApiResponse(300);
		$this->responseValidator->validateResponse($response);
	}

	public function testGetExpectedDeliveryDateReturnsDateTime()
	{
		$response = new ZboziApiResponse(200, [
			'expectedDeliveryDate' => '2012-01-01',
		]);
		$date = $this->responseValidator->getExpectedDeliveryDate($response);
		$this->assertInstanceOf('DateTime', $date);
		$this->assertSame('2012-01-01', $date->format('Y-m-d'));
	}

	/**
	 * @expectedException \SlevomatZboziApi\Response\ResponseErrorException
	 * @expectedExceptionMessage Slevomat API response doesn't contain expectedDeliveryDate.
	 */
	public function testGetExpectedDeliveryDateThrowsResponseExceptionForResponsesWithMissingExpectedDeliveryDateKey()
	{
		$response = new ZboziApiResponse(200, []);
		$this->responseValidator->getExpectedDeliveryDate($response);
	}

	/**
	 * @expectedException \SlevomatZboziApi\Response\ResponseErrorException
	 * @expectedExceptionMessage Slevomat API invalid response: invalid expectedDeliveryDate nonsense.
	 */
	public function testGetExpectedDeliveryDateThrowsResponseExceptionForResponsesWithInvalidExpectedDeliveryDateKey()
	{
		$response = new ZboziApiResponse(200, [
			'expectedDeliveryDate' => 'nonsense'
		]);
		$this->responseValidator->getExpectedDeliveryDate($response);
	}

	/**
	 * @expectedException \SlevomatZboziApi\Response\ResponseErrorException
	 * @expectedExceptionMessage Slevomat API invalid 400 response: missing status.
	 */
	public function testValidateResponseThrowsResponseErrorExceptionsWhenStatusKeyIsMissing()
	{
		$response = new ZboziApiResponse(400, [
			'messages' => ['someError'],
		]);
		$this->responseValidator->validateResponse($response);
	}

	/**
	 * @expectedException \SlevomatZboziApi\Response\ResponseErrorException
	 * @expectedExceptionMessage Slevomat API invalid 400 response: missing messages.
	 */
	public function testValidateResponseThrowsResponseErrorExceptionsWhenMessagesKeyIsMissing()
	{
		$response = new ZboziApiResponse(400, [
			'status' => InvalidRequestType::BAD_REQUEST,
		]);
		$this->responseValidator->validateResponse($response);
	}

	public function testValidateResponseConsidersAny200ResponseAsValid()
	{
		$this->responseValidator->validateResponse(new ZboziApiResponse(200));
		$this->responseValidator->validateResponse(new ZboziApiResponse(204));
		$this->assertTrue(true);
	}

	/**
	 * @param integer $httpStatusCode
	 * @param integer $clientErrorStatus
	 * @param string[] $messages
	 * @return \SlevomatZboziApi\Response\ZboziApiResponse
	 */
	private function createClientErrorResponse($httpStatusCode, $clientErrorStatus, $messages = ['some error'])
	{
		return new ZboziApiResponse(
			$httpStatusCode,
			[
				'status' => $clientErrorStatus,
				'messages' => $messages,
			]
		);
	}

}
