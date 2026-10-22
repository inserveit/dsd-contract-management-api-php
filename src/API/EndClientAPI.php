<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\API;

use Generator;
use Inserve\DSDContractManagementAPI\Contracts\EndClientsInterface;
use Inserve\DSDContractManagementAPI\Models\EndClient;
use Inserve\DSDContractManagementAPI\Models\PaginatedEndClients;
use Inserve\DSDContractManagementAPI\Options\EndClientFilters;
use InvalidArgumentException;

final class EndClientAPI extends AbstractAPIClient implements EndClientsInterface
{
    /**
     * @param int $id
     *
     * @return EndClient
     */
    public function get(int $id): EndClient
    {
        if ($id < 1) {
            throw new InvalidArgumentException('End client ID must be positive.');
        }

        $response = $this->apiClient->getEndClients('end-clients/' . $id);

        return $this->apiClient->denormalize($response, EndClient::class);
    }

    /**
     * @param int                   $offset
     * @param int                   $limit
     * @param EndClientFilters|null $filters
     *
     * @return PaginatedEndClients
     */
    public function list(int $offset = 0, int $limit = 20, ?EndClientFilters $filters = null): PaginatedEndClients
    {
        $this->validatePagination($offset, $limit);

        return $this->apiClient->denormalize(
            $this->apiClient->getEndClients(
                'end-clients',
                ['offset' => $offset, 'limit' => $limit] + ($filters?->toQuery() ?? []),
            ),
            PaginatedEndClients::class,
        );
    }

    /** @return Generator<int, EndClient> Fetches pages lazily. */
    public function all(?EndClientFilters $filters = null, int $limit = 100): Generator
    {
        $offset = 0;
        do {
            $page = $this->list($offset, $limit, $filters);
            foreach ($page->getResult() as $endClient) {
                yield $endClient;
            }
            $count = count($page->getResult());
            $offset += $count;
        } while ($count > 0 && $offset < $page->getPagination()->getTotalCount());
    }
}
