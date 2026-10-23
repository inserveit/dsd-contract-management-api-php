<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Enums;

enum CommitmentTerm: string
{
    case OneMonth = 'one_month';
    case OneYear = 'one_year';
    case ThreeYears = 'three_years';
}
