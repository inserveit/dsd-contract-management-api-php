<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\API;

use Generator;
use Inserve\DSDContractManagementAPI\Contracts\ContractsInterface;
use Inserve\DSDContractManagementAPI\Models\Contract;
use Inserve\DSDContractManagementAPI\Models\PaginatedContracts;
use Inserve\DSDContractManagementAPI\Options\ContractFilters;
use InvalidArgumentException;

final class ContractAPI extends AbstractAPIClient implements ContractsInterface
{
    /**
     * @param int $id
     *
     * @return Contract
     */
    public function get(int $id): Contract
    {
        if ($id < 1) {
            throw new InvalidArgumentException('Contract ID must be positive.');
        }

        $response = $this->apiClient->get('contracts/' . $id);

        return $this->apiClient->denormalize($response['result'] ?? [], Contract::class);
    }

    /**
     * @param int                  $offset
     * @param int                  $limit
     * @param ContractFilters|null $filters
     *
     * @return PaginatedContracts
     */
    public function list(int $offset = 0, int $limit = 20, ?ContractFilters $filters = null): PaginatedContracts
    {
        $this->validatePagination($offset, $limit);

        return $this->apiClient->denormalize(
            $this->apiClient->get('contracts', ['offset' => $offset, 'limit' => $limit] + ($filters?->toQuery() ?? [])),
            PaginatedContracts::class,
        );
    }

    /** @return Generator<int, Contract> Fetches pages lazily. */
    public function all(?ContractFilters $filters = null, int $limit = 100): Generator
    {
        $offset = 0;
        do {
            $page = $this->list($offset, $limit, $filters);
            foreach ($page->getResult() as $contract) {
                yield $contract;
            }
            $count = count($page->getResult());
            $offset += $count;
        } while ($count > 0 && $offset < $page->getPagination()->getTotalCount());
    }
}
