<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Models;

/**
 *
 */
final readonly class PaginatedEndClients
{
    /**
     * @param list<EndClient> $result
     */
    public function __construct(
        /** @var list<EndClient> */
        public array         $result,
        public ApiPagination $pagination,
    ) {
    }

    /**
     * @return EndClient[]
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
