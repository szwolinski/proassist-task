<?php

declare(strict_types=1);

namespace App\ServiceRequest\Enum;

enum TicketPriority: string
{
    case LOW = 'LOW';
    case MEDIUM = 'MEDIUM';
    case HIGH = 'HIGH';
    case CRITICAL = 'CRITICAL';

    public const array CHOICES = [
        self::LOW->value,
        self::MEDIUM->value,
        self::HIGH->value,
        self::CRITICAL->value,
    ];
}
