# DSD Contract Management API PHP client

A PHP 8.4+ wrapper for the [DSD Contract Management API v1](https://contract-management-api.dsdeurope.com/v1/doc).
Supports every currently documented endpoint:

| Method | Endpoint | Client method |
| --- | --- | --- |
| GET | `/v1/contracts` | `$api->contracts()->list()` |
| GET | `/v1/contracts/{id}` | `$api->contracts()->get($id)` |
| GET | `/v1/products` | `$api->products()->list()` |
| GET | `/v1/products/{sku}` | `$api->products()->get($sku)` |

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

## Development

```bash
composer install
composer test
```
