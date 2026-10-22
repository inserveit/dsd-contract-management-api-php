<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Options;

final readonly class EndClientFilters
{
    /**
     * @param bool|null   $active
     * @param string|null $name
     * @param string|null $firstName
     * @param string|null $lastName
     * @param string|null $company
     * @param string|null $postalCode
     * @param string|null $city
     * @param string|null $country
     * @param string|null $phone
     * @param string|null $email
     * @param string|null $numberCoc
     * @param string|null $numberVat
     */
    public function __construct(
        public ?bool $active = null,
        public ?string $name = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $company = null,
        public ?string $postalCode = null,
        public ?string $city = null,
        public ?string $country = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $numberCoc = null,
        public ?string $numberVat = null,
    ) {
    }

    /**
     * @return array
     */
    public function toQuery(): array
    {
        $query = [];
        foreach (get_object_vars($this) as $key => $value) {
            if ($value !== null) {
                $query[$key] = is_bool($value) ? ($value ? 'true' : 'false') : $value;
            }
        }

        return $query;
    }
}
