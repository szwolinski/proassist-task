<?php

namespace App\Tests\Api\Factory;

use App\ServiceRequest\Entity\Ticket;
use App\ServiceRequest\Enum\TicketPriority;
use App\ServiceRequest\Enum\TicketStatus;
use Override;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Ticket>
 */
final class TicketFactory extends PersistentObjectFactory
{
    #[Override]
    public static function class(): string
    {
        return Ticket::class;
    }

    #[Override]
    protected function defaults(): array|callable
    {
        return [
            'title' => self::faker()->sentence(4),
            'description' => self::faker()->text(300),
            'priority' => self::faker()->randomElement(TicketPriority::cases()),
            'status' => TicketStatus::NEW,
            'device' => DeviceFactory::new(),
        ];
    }
}
