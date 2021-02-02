Slevomat Zboží API PHP Library
======================

[![Build status](https://github.com/slevomat/zbozi-api-php-library/workflows/Build/badge.svg?branch=master)](https://github.com/slevomat/zbozi-api-php-library/actions?query=workflow%3ABuild+branch%3Amaster)
[![Latest Stable Version](https://img.shields.io/packagist/v/slevomat/zbozi-api-library.svg)](https://packagist.org/packages/slevomat/zbozi-api-library)
[![Code coverage](https://codecov.io/gh/slevomat/zbozi-api-php-library/branch/master/graph/badge.svg)](https://codecov.io/gh/slevomat/zbozi-api-php-library)

[Dokumentace Zboží API](https://www.slevomat.cz/partner/zbozi-api)

Tato knihovna slouží pro implementaci komunikace Partner -> Slevomat.

Knihovna vyžaduje verzi PHP 7.4 nebo vyšší a předpokládá využití nástroje [Composer](https://getcomposer.org/).

Instalace knihovny
--------------------

```
composer require slevomat/zbozi-api-library
```

Použití knihovny
--------------------

V repozitáři se nachází soubor `examples.php` s ukázkovým použitím. API se volá skrze metody na objektu `\SlevomatZboziApi\ZboziApiClient`.

Objekt se vytvoří nejsnáze pomocí továrničky:

```
$client = \SlevomatZboziApi\ZboziApiClientFactory::create($partnerToken, $apiSecret, $apiUrl, $timeout, $logger); // logger a timeout jsou nepovinné
```

Při volání metod klienta se volá API Slevomatu. Např.:

```
$expectedDeliveryDate = $client->markGettingReadyForPickup($orderId);
```

Chybové stavy
----------------------

Při volání API může dojít k řadě chyb. Vyhazují se následující výjimky:

* `\SlevomatZboziApi\Request\ConnectionErrorException` - nepodařilo se připojit na API, požadavek zopakujte

V případě, že se na API podaří připojit, může knihovna vyhodit následující chyby (všechny jsou typu `\SlevomatZboziApi\Request\InvalidRequestException`):

* `\SlevomatZboziApi\Request\InvalidCredentialsException` - neplatné přihlašovací údaje
* `\SlevomatZboziApi\Request\OrderNotFoundException` - neexistující objednávka
* `\SlevomatZboziApi\Request\OrderItemNotFoundException` - neexistující položka objednávky
* `\SlevomatZboziApi\Request\InvalidStatusChangeException` - přechod objednávky do nepovoleného stavu
* `\SlevomatZboziApi\Request\InvalidCancelException` - neplatné storno - stornování většího počtu položek, než existuje
* `\SlevomatZboziApi\Request\OtherRequestErrorException` - jiná chyba
* `\SlevomatZboziApi\Request\OrderNotExportedException` - objednávka nebyla ještě exportována do partnerského API - nelze s ní skrze API manipulovat

Všechny výjimky `InvalidRequestException` mají metodu `getMessages()`, která slouží k získání chybových zpráv, které vrátil server.

U těchto chyb je potřeba před opakovaný pokusem opravit odesílaný požadavek. V případě odesílání stejného požadavku dojde pravděpodobně ke stejné chybě.

Všechny výjimky v knihovně implementují interface `SlevomatZboziApi\ZboziApiException`, pokud nepotřebujete reagovat na každý chybový stav zvláštním způsobem, lze chytat tento typ.

Logování požadavků
--------------------

Knihovna podporuje volitelně logování požadavků. Pro implementaci logování použite interface `\SlevomatZboziApi\ZboziApiLogger`.

Ukázkový logger, který informace o požadavcích rovnou vypisuje, je v repozitáři v souboru `ExampleLogger.php`.
