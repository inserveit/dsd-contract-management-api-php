<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Contracts;

use Inserve\DSDContractManagementAPI\Models\EndClient;
use Inserve\DSDContractManagementAPI\Models\PaginatedEndClients;
use Inserve\DSDContractManagementAPI\Options\EndClientFilters;

interface EndClientsInterface
{
    /**
     * @param int $id
     *
     * @return EndClient
     */
    public function get(int $id): EndClient;

    /**
     * @param int                   $offset
     * @param int                   $limit
     * @param EndClientFilters|null $filters
     *
     * @return PaginatedEndClients
     */
    public function list(int $offset = 0, int $limit = 20, ?EndClientFilters $filters = null): PaginatedEndClients;

    /**
     * @param EndClientFilters|null $filters
     * @param int                   $limit
     *
     * @return iterable
     */
    public function all(?EndClientFilters $filters = null, int $limit = 100): iterable;
}
