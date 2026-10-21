<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Models;

final readonly class PriceCatalogue
{
    /**
     * @param string      $unit
     * @param string      $currency
     * @param float       $advisoryPrice
     * @param float       $discountPercentage
     * @param float       $price
     * @param string|null $commitmentTerm
     * @param string|null $invoicingPeriod
     * @param int|null    $min
     * @param int|null    $max
     */
    public function __construct(
        public string  $unit,
        public string  $currency,
        public float   $advisoryPrice,
        public float   $discountPercentage,
        public float   $price,
        public ?string $commitmentTerm = null,
        public ?string $invoicingPeriod = null,
        public ?int    $min = null,
        public ?int    $max = null,
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
     * @return string|null
     */
    public function getCommitmentTerm(): ?string
    {
        return $this->commitmentTerm;
    }

    /**
     * @return string|null
     */
    public function getInvoicingPeriod(): ?string
    {
        return $this->invoicingPeriod;
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
    public function getMin(): ?int
    {
        return $this->min;
    }

    /**
     * @return int|null
     */
    public function getMax(): ?int
    {
        return $this->max;
    }
}
