<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Models;

final readonly class PaginatedProducts
{
    /**
     * @param list<ProductCatalogue> $result
     */
    public function __construct(
        /** @var list<ProductCatalogue> */
        public array         $result,
        public ApiPagination $pagination,
    ) {
    }

    /** @return list<ProductCatalogue> */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * @return ApiPagination
     */
    public function getPagination(): ApiPagination
    {
        return $this->pagination;
    }
}
