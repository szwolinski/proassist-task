<?php

namespace App\Tests\Api\Factory;

use App\ServiceRequest\Entity\TicketHistory;
use App\ServiceRequest\Enum\TicketStatus;
use Override;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<TicketHistory>
 */
final class TicketHistoryFactory extends PersistentObjectFactory
{
    #[Override]
    public static function class(): string
    {
        return TicketHistory::class;
    }

    #[Override]
    protected function defaults(): array|callable
    {
        return [
            'newStatus' => self::faker()->randomElement(TicketStatus::cases()),
            'ticket' => null,
        ];
    }

    #[Override]
    protected function initialize(): static
    {
        return $this;
    }
}
