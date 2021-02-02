<?php declare(strict_types = 1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Europe/Prague');

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/ExampleLogger.php';

$partnerToken = '__DOPLNIT__';
$apiSecret = '__DOPLNIT__';
$apiUrl = 'https://www.slevomat.cz/zbozi-api/v1-test';
$logger = new ExampleLogger;

$testClient = \SlevomatZboziApi\ZboziApiClientFactory::create($partnerToken, $apiSecret, $apiUrl, 30, $logger);

try {
	$orderId = '7048475959';

	echo '<h1>Slevomat Zboží API examples</h1>';

	printTitle('mark-pending');
	$testClient->markPending($orderId);
	printSuccess();

	printTitle('mark-en-route');
	$expectedDeliveryDate = $testClient->markEnRoute($orderId, true);
	printSuccess(sprintf(' (expectedDeliveryDate: %s)', $expectedDeliveryDate->format('j. n. Y')));

	printTitle('mark-getting-ready-for-pickup');
	$expectedDeliveryDate = $testClient->markGettingReadyForPickup($orderId, true, true);
	printSuccess(sprintf(' (expectedDeliveryDate: %s)', $expectedDeliveryDate->format('j. n. Y')));

	printTitle('mark-ready-for-pickup');
	$testClient->markReadyForPickup($orderId, true);
	printSuccess();

	printTitle('mark-delivered');
	$testClient->markDelivered($orderId);
	printSuccess();

	printTitle('cancel');
	$cancelOrderItems = [
		new \SlevomatZboziApi\Request\CancelOrderItem('787887454', 1),
		new \SlevomatZboziApi\Request\CancelOrderItem('7844544', 2),
	];
	$testClient->cancelOrder($orderId, $cancelOrderItems, 'Duvod storna');
	printSuccess();

	printTitle('update-shipping-address');
	$testClient->updateShippingAddress($orderId, 'Petr Novak', 'Prazska 16', 'Praha 10', 'cz', '+420777888999', '10200');
	printSuccess();

} catch (\SlevomatZboziApi\Request\ConnectionErrorException $e) {
	printError(sprintf('%s %s', $e->getMessage(), $e->getPrevious()->getMessage()));
	//retry request

} catch (\SlevomatZboziApi\Request\InvalidRequestException $e) {
	printError($e->getMessage(), 'red', 'REQUEST ERROR: ');
	//fix error

} catch (\SlevomatZboziApi\ZboziApiException $e) {
	printError($e->getMessage());
	//retry request
}

function printError($message, $color = 'orangered', $startWith = '')
{
	echo sprintf('<br><b style="color: %s">%s%s</b><hr>', $color, $startWith, $message);
}

function printSuccess($additionalInfo = '')
{
	echo sprintf('<br><b style="color: green"> OK%s</b><hr>', $additionalInfo);
}

function printTitle($title)
{
	echo sprintf('<br><b style="background-color: darkslategray; color: white; padding: 5px">%s</b><br>', $title);
}

