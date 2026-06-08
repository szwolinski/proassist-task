<?php

declare(strict_types=1);

namespace App\ServiceRequest\Enum;

enum TicketStatus: string
{
    case NEW = 'NEW';
    case ASSIGNED = 'ASSIGNED';
    case IN_PROGRESS = 'IN_PROGRESS';
    case DONE = 'DONE';
    case CANCELLED = 'CANCELLED';

    public const array CHOICES = [
        self::NEW->value,
        self::ASSIGNED->value,
        self::IN_PROGRESS->value,
        self::DONE->value,
        self::CANCELLED->value,
    ];
}
