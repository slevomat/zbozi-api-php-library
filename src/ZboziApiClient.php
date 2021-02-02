<?php declare(strict_types = 1);

namespace SlevomatZboziApi;

use DateTime;
use SlevomatZboziApi\Request\CancelOrderItem;
use SlevomatZboziApi\Request\RequestMaker;
use SlevomatZboziApi\Response\ResponseValidator;
use SlevomatZboziApi\Type\TypeValidator;
use function sprintf;

class ZboziApiClient
{

	private ResponseValidator $responseValidator;

	private RequestMaker $requestMaker;

	private string $apiUrl;

	public function __construct(RequestMaker $requestMaker, ResponseValidator $responseValidator, string $apiUrl)
	{
		TypeValidator::checkString($apiUrl);

		$this->responseValidator = $responseValidator;
		$this->requestMaker = $requestMaker;
		$this->apiUrl = $apiUrl;
	}

	/**
	 * @param string $orderId
	 * @param CancelOrderItem[] $cancelOrderItems
	 * @param string|null $note
	 */
	public function cancelOrder(string $orderId, array $cancelOrderItems, ?string $note = null): void
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

	public function markPending(string $orderId): void
	{
		TypeValidator::checkString($orderId);

		$endpoint = $this->getEndpoint($orderId, 'mark-pending');
		$response = $this->requestMaker->sendPostRequest($endpoint);
		$this->responseValidator->validateResponse($response);
	}

	public function markEnRoute(string $orderId, bool $autoMarkDelivered): DateTime
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

	public function markGettingReadyForPickup(string $orderId, bool $autoMarkReadyForPickup = false, bool $autoMarkDelivered = false): DateTime
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

	public function markReadyForPickup(string $orderId, bool $autoMarkDelivered = false): void
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

	public function markDelivered(string $orderId): void
	{
		TypeValidator::checkString($orderId);

		$endpoint = $this->getEndpoint($orderId, 'mark-delivered');
		$response = $this->requestMaker->sendPostRequest($endpoint);
		$this->responseValidator->validateResponse($response);
	}

	public function updateShippingAddress(
		string $orderId,
		string $name,
		string $street,
		string $city,
		string $state,
		string $phone,
		string $postalCode,
		?string $company = null
	): void
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

	private function getEndpoint(string $orderId, string $action): string
	{
		return sprintf('%s/order/%s/%s', $this->apiUrl, $orderId, $action);
	}

}
