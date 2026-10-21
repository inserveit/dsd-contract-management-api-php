<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Inserve\DSDContractManagementAPI\API\ContractAPI;
use Inserve\DSDContractManagementAPI\API\ProductAPI;
use Inserve\DSDContractManagementAPI\Client\APIClient;
use Inserve\DSDContractManagementAPI\Contracts\ContractsInterface;
use Inserve\DSDContractManagementAPI\Contracts\ProductsInterface;
use Inserve\DSDContractManagementAPI\Serialization\SerializerFactory;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use SensitiveParameter;

final class DSDContractManagementAPIClient
{
    public const string DEFAULT_BASE_URI = APIClient::DEFAULT_BASE_URI;

    private readonly ContractsInterface $contracts;
    private readonly ProductsInterface $products;

    /**
     * @param APIClient $apiClient
     */
    public function __construct(private readonly APIClient $apiClient)
    {
        $this->contracts = new ContractAPI($apiClient);
        $this->products = new ProductAPI($apiClient);
    }

    /**
     * @param string               $accessToken
     * @param LoggerInterface|null $logger
     * @param string               $baseUri
     * @param float                $timeout
     *
     * @return self
     */
    public static function create(
        #[SensitiveParameter] string $accessToken,
        ?LoggerInterface $logger = null,
        string $baseUri = self::DEFAULT_BASE_URI,
        float $timeout = 30.0,
    ): self {
        if ($timeout <= 0 || !is_finite($timeout)) {
            throw new InvalidArgumentException('Timeout must be a finite positive number.');
        }

        return new self(new APIClient(
            client: new Client(['timeout' => $timeout, 'connect_timeout' => min(10.0, $timeout)]),
            requestFactory: new HttpFactory(),
            accessToken: $accessToken,
            denormalizer: SerializerFactory::create(),
            logger: $logger,
            baseUri: $baseUri,
        ));
    }

    /**
     * @return ContractsInterface
     */
    public function contracts(): ContractsInterface
    {
        return $this->contracts;
    }

    /**
     * @return ProductsInterface
     */
    public function products(): ProductsInterface
    {
        return $this->products;
    }

    /**
     * @param string $accessToken
     *
     * @return void
     */
    public function setAccessToken(#[SensitiveParameter] string $accessToken): void
    {
        $this->apiClient->setAccessToken($accessToken);
    }
}
