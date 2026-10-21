<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Models;

final readonly class ContractTotals
{
    /**
     * @param float  $totalPrice
     * @param string $currency
     */
    public function __construct(
        public float  $totalPrice,
        public string $currency,
    ) {
    }

    /**
     * @return float
     */
    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }
}
