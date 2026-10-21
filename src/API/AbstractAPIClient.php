<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\API;

use Inserve\DSDContractManagementAPI\Client\APIClient;
use InvalidArgumentException;

abstract class AbstractAPIClient
{
    /**
     * @param APIClient $apiClient
     */
    public function __construct(protected readonly APIClient $apiClient)
    {
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return void
     */
    protected function validatePagination(int $offset, int $limit): void
    {
        if ($offset < 0 || $limit < 1 || $limit > 100) {
            throw new InvalidArgumentException('Offset must be non-negative and limit must be between 1 and 100.');
        }
    }
}
