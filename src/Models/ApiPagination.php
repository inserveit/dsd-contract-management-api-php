<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Models;

final readonly class ApiPagination
{
    /**
     * @param int $offset
     * @param int $resultCount
     * @param int $totalCount
     */
    public function __construct(
        public int $offset,
        public int $resultCount,
        public int $totalCount,
    ) {
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function getResultCount(): int
    {
        return $this->resultCount;
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }
}
