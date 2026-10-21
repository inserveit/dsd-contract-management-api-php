<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Contracts;

use Inserve\DSDContractManagementAPI\Models\PaginatedProducts;
use Inserve\DSDContractManagementAPI\Models\ProductCatalogue;

interface ProductsInterface
{
    /**
     * @param string $sku
     *
     * @return ProductCatalogue
     */
    public function get(string $sku): ProductCatalogue;

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return PaginatedProducts
     */
    public function list(int $offset = 0, int $limit = 20): PaginatedProducts;

    /** @return iterable<int, ProductCatalogue> */
    public function all(int $limit = 100): iterable;
}
