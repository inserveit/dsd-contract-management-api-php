<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Models;

final readonly class PriceContract
{
    /**
     * @param string   $unit
     * @param string   $currency
     * @param float    $advisoryPrice
     * @param float    $discountPercentage
     * @param float    $price
     * @param int|null $quantity
     */
    public function __construct(
        public string $unit,
        public string $currency,
        public float  $advisoryPrice,
        public float  $discountPercentage,
        public float  $price,
        public ?int   $quantity = null,
    ) {
    }

    /**
     * @return string
     */
    public function getUnit(): string
    {
        return $this->unit;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return float
     */
    public function getAdvisoryPrice(): float
    {
        return $this->advisoryPrice;
    }

    /**
     * @return float
     */
    public function getDiscountPercentage(): float
    {
        return $this->discountPercentage;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @return int|null
     */
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }
}
