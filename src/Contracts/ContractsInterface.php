<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Contracts;

use Inserve\DSDContractManagementAPI\Models\Contract;
use Inserve\DSDContractManagementAPI\Models\PaginatedContracts;
use Inserve\DSDContractManagementAPI\Options\ContractFilters;

interface ContractsInterface
{
    /**
     * @param int $id
     *
     * @return Contract
     */
    public function get(int $id): Contract;

    /**
     * @param int                  $offset
     * @param int                  $limit
     * @param ContractFilters|null $filters
     *
     * @return PaginatedContracts
     */
    public function list(int $offset = 0, int $limit = 20, ?ContractFilters $filters = null): PaginatedContracts;

    /** @return iterable<int, Contract> */
    public function all(?ContractFilters $filters = null, int $limit = 100): iterable;
}
