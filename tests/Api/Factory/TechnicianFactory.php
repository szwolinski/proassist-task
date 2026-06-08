<?php

namespace App\Tests\Api\Factory;

use App\ServiceRequest\Entity\Technician;
use Override;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Technician>
 */
final class TechnicianFactory extends PersistentObjectFactory
{
    #[Override]
    public static function class(): string
    {
        return Technician::class;
    }

    #[Override]
    protected function defaults(): array|callable
    {
        return [
            'active' => self::faker()->boolean(),
            'email' => self::faker()->text(180),
            'firstName' => self::faker()->text(100),
            'lastName' => self::faker()->text(100),
        ];
    }
}
