<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\API;

use Generator;
use Inserve\DSDContractManagementAPI\Contracts\ProductsInterface;
use Inserve\DSDContractManagementAPI\Models\PaginatedProducts;
use Inserve\DSDContractManagementAPI\Models\ProductCatalogue;

final class ProductAPI extends AbstractAPIClient implements ProductsInterface
{
    /**
     * @param string $sku
     *
     * @return ProductCatalogue
     */
    public function get(string $sku): ProductCatalogue
    {
        if (trim($sku) === '' || $sku === '.' || $sku === '..') {
            throw new \InvalidArgumentException('A non-empty product SKU is required.');
        }

        $response = $this->apiClient->get('products/' . rawurlencode($sku));

        return $this->apiClient->denormalize($response['result'] ?? [], ProductCatalogue::class);
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return PaginatedProducts
     */
    public function list(int $offset = 0, int $limit = 20): PaginatedProducts
    {
        $this->validatePagination($offset, $limit);

        return $this->apiClient->denormalize(
            $this->apiClient->get('products', ['offset' => $offset, 'limit' => $limit]),
            PaginatedProducts::class,
        );
    }

    /** @return Generator<int, ProductCatalogue> Fetches pages lazily. */
    public function all(int $limit = 100): Generator
    {
        $offset = 0;
        do {
            $page = $this->list($offset, $limit);
            foreach ($page->getResult() as $product) {
                yield $product;
            }
            $count = count($page->getResult());
            $offset += $count;
        } while ($count > 0 && $offset < $page->getPagination()->getTotalCount());
    }
}
