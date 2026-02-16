<?php

declare(strict_types=1);

namespace App\Enum;

enum Role: string
{
    case ROLE_USER = 'ROLE_USER';
    case ROLE_DOCTOR = 'ROLE_DOCTOR';
    case ROLE_NURSE = 'ROLE_NURSE';
    case ROLE_PATIENT = 'ROLE_PATIENT';
    case ROLE_MANAGER = 'ROLE_MANAGER';
}
