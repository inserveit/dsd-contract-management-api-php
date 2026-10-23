<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Enums;

enum InvoicingPeriod: string
{
    case OneTime = 'one_time';
    case OneMonth = 'one_month';
    case OneYear = 'one_year';
    case ThreeYears = 'three_years';
}
