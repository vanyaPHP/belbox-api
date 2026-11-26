<?php

declare(strict_types=1);

namespace App\Enums\HR;

enum EmployeeStatus: string
{
    case Pending = 'pending';
    case Rejected = 'rejected';
    case Hired = 'hired';
    case Active = 'active';
    case Fired = 'fired';
}
