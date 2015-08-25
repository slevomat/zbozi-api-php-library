<?php

namespace SlevomatZboziApi\Response;

use SlevomatZboziApi\Request\InvalidRequestType;

class ResponseValidator
{

	public function validateResponse(ZboziApiResponse $response)
	{
		if (preg_match('~^2~', $response->getStatusCode())) {
			return;
		}

		$responseBody = $response->getBody();

		if (preg_match('~^4~', $response->getStatusCode())) {
			if (!isset($responseBody['status'])) {
				throw new \SlevomatZboziApi\Response\ResponseErrorException(sprintf('Slevomat API invalid %s response: missing status.', $response->getStatusCode()));
			}
			if (!isset($responseBody['messages'])) {
				throw new \SlevomatZboziApi\Response\ResponseErrorException(sprintf('Slevomat API invalid %s response: missing messages.', $response->getStatusCode()));
			}
			switch ($responseBody['status']) {
				case InvalidRequestType::BAD_REQUEST:
					throw new \SlevomatZboziApi\Request\BadRequestException($responseBody['messages']);

				case InvalidRequestType::INVALID_CREDENTIALS:
					throw new \SlevomatZboziApi\Request\InvalidCredentialsException($responseBody['messages']);

				case InvalidRequestType::ORDER_NOT_FOUND:
					throw new \SlevomatZboziApi\Request\OrderNotFoundException($responseBody['messages']);

				case InvalidRequestType::ORDER_ITEM_NOT_FOUND:
					throw new \SlevomatZboziApi\Request\OrderItemNotFoundException($responseBody['messages']);

				case InvalidRequestType::INVALID_STATUS_CHANGE:
					throw new \SlevomatZboziApi\Request\InvalidStatusChangeException($responseBody['messages']);

				case InvalidRequestType::INVALID_CANCEL:
					throw new \SlevomatZboziApi\Request\InvalidCancelException($responseBody['messages']);

				case InvalidRequestType::OTHER_ERROR:
					throw new \SlevomatZboziApi\Request\OtherRequestErrorException($responseBody['messages']);

				case InvalidRequestType::ORDER_NOT_EXPORTED:
					throw new \SlevomatZboziApi\Request\OrderNotExportedException($responseBody['messages']);

				default:
					throw new \SlevomatZboziApi\Response\ResponseErrorException(sprintf('Slevomat API %s response contains unknown status %s.', $response->getStatusCode(), $responseBody['status']));
			}
		}

		throw new \SlevomatZboziApi\Response\ResponseErrorException(sprintf('Slevomat API responded with unexpected HTTP status code: %s.', $response->getStatusCode()));
	}

	/**
	 * @param \SlevomatZboziApi\Response\ZboziApiResponse $response
	 * @return \DateTimeImmutable
	 */
	public function getExpectedDeliveryDate(ZboziApiResponse $response)
	{
		$body = $response->getBody();

		if (!isset($body['expectedDeliveryDate'])) {
			throw new \SlevomatZboziApi\Response\ResponseErrorException('Slevomat API response doesn\'t contain expectedDeliveryDate.');
		}

		$date = \DateTimeImmutable::createFromFormat('Y-m-d', $body['expectedDeliveryDate']);
		if ($date === false) {
			throw new \SlevomatZboziApi\Response\ResponseErrorException(sprintf('Slevomat API invalid response: invalid expectedDeliveryDate %s.', $body['expectedDeliveryDate']));
		}

		return $date;
	}

}
