<?php

namespace SlevomatZboziApi;

use SlevomatZboziApi\Request\RequestMaker;
use SlevomatZboziApi\Response\ResponseValidator;
use SlevomatZboziApi\Type\TypeValidator;

class ZboziApiClient
{

	/** @var ResponseValidator */
	private $responseValidator;

	/** @var RequestMaker */
	private $requestMaker;

	/** @var string */
	private $apiUrl;

	/**
	 * @param RequestMaker $requestMaker
	 * @param ResponseValidator $responseValidator
	 * @param string $apiUrl
	 */
	public function __construct(RequestMaker $requestMaker, ResponseValidator $responseValidator, $apiUrl)
	{
		TypeValidator::checkString($apiUrl);

		$this->responseValidator = $responseValidator;
		$this->requestMaker = $requestMaker;
		$this->apiUrl = $apiUrl;
	}

	/**
	 * @param string $orderId
	 * @param \SlevomatZboziApi\Request\CancelOrderItem[] $cancelOrderItems
	 * @param null|string $note
	 */
	public function cancelOrder($orderId, array $cancelOrderItems, $note = null)
	{
		TypeValidator::checkString($orderId);
		TypeValidator::checkArray($cancelOrderItems, 'SlevomatZboziApi\Request\CancelOrderItem');
		TypeValidator::checkString($note, true);

		$endpoint = $this->getEndpoint($orderId, 'cancel');
		$body = [
			'items' => $cancelOrderItems,
			'note' => $note,
		];
		$response = $this->requestMaker->sendPostRequest($endpoint, $body);
		$this->responseValidator->validateResponse($response);
	}

	/**
	 * @param string $orderId
	 */
	public function markPending($orderId)
	{
		TypeValidator::checkString($orderId);

		$endpoint = $this->getEndpoint($orderId, 'mark-pending');
		$response = $this->requestMaker->sendPostRequest($endpoint);
		$this->responseValidator->validateResponse($response);
	}

	/**
	 * @param string $orderId
	 * @param boolean $autoMarkDelivered
	 * @return \DateTime
	 */
	public function markEnRoute($orderId, $autoMarkDelivered)
	{
		TypeValidator::checkString($orderId);
		TypeValidator::checkBoolean($autoMarkDelivered);

		$endpoint = $this->getEndpoint($orderId, 'mark-en-route');
		$body = [
			'autoMarkDelivered' => $autoMarkDelivered,
		];
		$response = $this->requestMaker->sendPostRequest($endpoint, $body);
		$this->responseValidator->validateResponse($response);

		return $this->responseValidator->getExpectedDeliveryDate($response);
	}

	/**
	 * @param string $orderId
	 * @param boolean $autoMarkReadyForPickup
	 * @param boolean $autoMarkDelivered
	 * @return \DateTime
	 */
	public function markGettingReadyForPickup($orderId, $autoMarkReadyForPickup = false, $autoMarkDelivered = false)
	{
		TypeValidator::checkString($orderId);
		TypeValidator::checkBoolean($autoMarkReadyForPickup);
		TypeValidator::checkBoolean($autoMarkDelivered);

		$endpoint = $this->getEndpoint($orderId, 'mark-getting-ready-for-pickup');
		$body = [
			'autoMarkDelivered' => $autoMarkDelivered,
			'autoMarkReadyForPickup' => $autoMarkReadyForPickup,
		];
		$response = $this->requestMaker->sendPostRequest($endpoint, $body);
		$this->responseValidator->validateResponse($response);

		return $this->responseValidator->getExpectedDeliveryDate($response);
	}

	/**
	 * @param string $orderId
	 * @param boolean $autoMarkDelivered
	 */
	public function markReadyForPickup($orderId, $autoMarkDelivered = false)
	{
		TypeValidator::checkString($orderId);
		TypeValidator::checkBoolean($autoMarkDelivered);

		$endpoint = $this->getEndpoint($orderId, 'mark-ready-for-pickup');
		$body = [
			'autoMarkDelivered' => $autoMarkDelivered,
		];
		$response = $this->requestMaker->sendPostRequest($endpoint, $body);
		$this->responseValidator->validateResponse($response);
	}

	/**
	 * @param string $orderId
	 */
	public function markDelivered($orderId)
	{
		TypeValidator::checkString($orderId);

		$endpoint = $this->getEndpoint($orderId, 'mark-delivered');
		$response = $this->requestMaker->sendPostRequest($endpoint);
		$this->responseValidator->validateResponse($response);
	}

	/**
	 * @param string $orderId
	 * @param string $name
	 * @param string $street
	 * @param string $city
	 * @param string  $state
	 * @param string $phone
	 * @param string $postalCode
	 * @param string|null $company
	 */
	public function updateShippingAddress($orderId, $name, $street, $city, $state, $phone, $postalCode, $company = null)
	{
		TypeValidator::checkString($orderId);
		TypeValidator::checkString($name);
		TypeValidator::checkString($street);
		TypeValidator::checkString($city);
		TypeValidator::checkString($state);
		TypeValidator::checkString($phone);
		TypeValidator::checkString($postalCode);
		if ($company !== null) {
			TypeValidator::checkString($company);
		}

		$body = [
			'name' => $name,
			'street' => $street,
			'city' => $city,
			'state' => $state,
			'phone' => $phone,
			'postalCode' => $postalCode,
			'company' => $company,
		];

		$endpoint = $this->getEndpoint($orderId, 'update-shipping-address');
		$response = $this->requestMaker->sendPostRequest($endpoint, $body);
		$this->responseValidator->validateResponse($response);
	}

	/**
	 * @param string $orderId
	 * @param string $action
	 * @return string
	 */
	private function getEndpoint($orderId, $action)
	{
		return sprintf('%s/order/%s/%s', $this->apiUrl, $orderId, $action);
	}

}
