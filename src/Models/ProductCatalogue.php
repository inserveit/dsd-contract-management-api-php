<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Models;

final readonly class ProductCatalogue
{
    /**
     * @param list<PriceCatalogue> $prices
     */
    public function __construct(
        public string  $sku,
        public string  $name,
        /** @var list<PriceCatalogue> */
        public array   $prices,
        public ?string $brand = null,
        public ?string $ean = null,
        public ?string $productType = null,
    ) {
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getBrand(): ?string
    {
        return $this->brand;
    }

    /**
     * @return string|null
     */
    public function getEan(): ?string
    {
        return $this->ean;
    }

    /**
     * @return string|null
     */
    public function getProductType(): ?string
    {
        return $this->productType;
    }

    /** @return list<PriceCatalogue> */
    public function getPrices(): array
    {
        return $this->prices;
    }
}
