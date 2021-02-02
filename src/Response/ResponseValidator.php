<?php declare(strict_types = 1);

namespace SlevomatZboziApi\Response;

use DateTime;
use SlevomatZboziApi\Request\BadRequestException;
use SlevomatZboziApi\Request\InvalidCancelException;
use SlevomatZboziApi\Request\InvalidCredentialsException;
use SlevomatZboziApi\Request\InvalidRequestType;
use SlevomatZboziApi\Request\InvalidStatusChangeException;
use SlevomatZboziApi\Request\OrderItemNotFoundException;
use SlevomatZboziApi\Request\OrderNotExportedException;
use SlevomatZboziApi\Request\OrderNotFoundException;
use SlevomatZboziApi\Request\OtherRequestErrorException;
use function preg_match;
use function sprintf;

class ResponseValidator
{

	public function validateResponse(ZboziApiResponse $response): void
	{
		if (preg_match('~^2~', (string) $response->getStatusCode()) === 1) {
			return;
		}

		$responseBody = $response->getBody();

		if (preg_match('~^4~', (string) $response->getStatusCode()) === 1) {
			if (!isset($responseBody['status'])) {
				throw new ResponseErrorException(sprintf('Slevomat API invalid %s response: missing status.', $response->getStatusCode()));
			}
			if (!isset($responseBody['messages'])) {
				throw new ResponseErrorException(sprintf('Slevomat API invalid %s response: missing messages.', $response->getStatusCode()));
			}

			switch ($responseBody['status']) {
				case InvalidRequestType::BAD_REQUEST:
					throw new BadRequestException($responseBody['messages']);
				case InvalidRequestType::INVALID_CREDENTIALS:
					throw new InvalidCredentialsException($responseBody['messages']);
				case InvalidRequestType::ORDER_NOT_FOUND:
					throw new OrderNotFoundException($responseBody['messages']);
				case InvalidRequestType::ORDER_ITEM_NOT_FOUND:
					throw new OrderItemNotFoundException($responseBody['messages']);
				case InvalidRequestType::INVALID_STATUS_CHANGE:
					throw new InvalidStatusChangeException($responseBody['messages']);
				case InvalidRequestType::INVALID_CANCEL:
					throw new InvalidCancelException($responseBody['messages']);
				case InvalidRequestType::OTHER_ERROR:
					throw new OtherRequestErrorException($responseBody['messages']);
				case InvalidRequestType::ORDER_NOT_EXPORTED:
					throw new OrderNotExportedException($responseBody['messages']);
				default:
					throw new ResponseErrorException(sprintf('Slevomat API %s response contains unknown status %s.', $response->getStatusCode(), $responseBody['status']));
			}
		}

		throw new ResponseErrorException(sprintf('Slevomat API responded with unexpected HTTP status code: %s.', $response->getStatusCode()));
	}

	public function getExpectedDeliveryDate(ZboziApiResponse $response): DateTime
	{
		$body = $response->getBody();

		if (!isset($body['expectedDeliveryDate'])) {
			throw new ResponseErrorException('Slevomat API response doesn\'t contain expectedDeliveryDate.');
		}

		$date = DateTime::createFromFormat('Y-m-d', $body['expectedDeliveryDate']);
		if ($date === false) {
			throw new ResponseErrorException(sprintf('Slevomat API invalid response: invalid expectedDeliveryDate %s.', $body['expectedDeliveryDate']));
		}

		return $date;
	}

}
