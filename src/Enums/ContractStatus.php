<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Enums;

enum ContractStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Canceled = 'canceled';
    case Ended = 'ended';
    case Deleted = 'deleted';
}
