<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Models;

final readonly class PaginatedContracts
{
    /**
     * @param list<Contract> $result
     */
    public function __construct(
        /** @var list<Contract> */
        public array         $result,
        public ApiPagination $pagination,
    ) {
    }

    /**
     * @return Contract[]
     */
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
