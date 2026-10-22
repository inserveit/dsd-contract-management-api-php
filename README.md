# DSD Contract Management API PHP client

A PHP 8.4+ wrapper for the [DSD Contract Management API v1](https://contract-management-api.dsdeurope.com/v1/doc).
Also supports listing and retrieving clients from the [DSD End Client API v1](https://end-client-api.dsdeurope.com/v1/doc).

| Method | Endpoint | Client method |
| --- | --- | --- |
| GET | `/v1/contracts` | `$api->contracts()->list()` |
| GET | `/v1/contracts/{id}` | `$api->contracts()->get($id)` |
| GET | `/v1/products` | `$api->products()->list()` |
| GET | `/v1/products/{sku}` | `$api->products()->get($sku)` |
| GET | `/v1/end-clients` | `$api->endClients()->list()` |
| GET | `/v1/end-clients/{clientId}` | `$api->endClients()->get($clientId)` |

The documentation specifies bearer authentication and does not document a token issuance or refresh endpoint. Obtain your token from DSD.

## Installation

```bash
composer require inserve/dsd-contract-management-api-php`
```

Inject the client into a controller, service, or job:

```php
use Inserve\DSDContractManagementAPI\DSDContractManagementAPIClient;
use Inserve\DSDContractManagementAPI\Options\ContractFilters;

$token = 'access.token';
$api = DSDContractManagementAPIClient::create(accessToken: $token);

var_dump($api->contracts()->list(offset: 0, limit: 1));
```

End-client requests use `https://end-client-api.dsdeurope.com` and share the access token with contracts and products, including updates through `setAccessToken()`. The existing `baseUri` option still applies to contracts and products; use `endClientBaseUri` in `create()` or the injected `APIClient` to override the end-client origin.

```php
use Inserve\DSDContractManagementAPI\Options\EndClientFilters;

$page = $api->endClients()->list(
    offset: 0,
    limit: 20,
    filters: new EndClientFilters(active: true, company: 'Example BV'),
);
$client = $api->endClients()->get(7);

foreach ($api->endClients()->all(new EndClientFilters(country: 'NL')) as $client) {
    echo $client->getName();
}
```

`EndClientFilters` supports `active`, `name`, `firstName`, `lastName`, `company`, `postalCode`, `city`, `country`, `phone`, `email`, `numberCoc`, and `numberVat`. List responses expose `getResult()` and `getPagination()`; `all()` fetches pages lazily.

## Development

```bash
composer install
composer test
```
