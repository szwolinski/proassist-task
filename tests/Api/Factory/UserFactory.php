<?php

namespace App\Tests\Api\Factory;

use App\ServiceRequest\Entity\User;
use Override;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<User>
 */
final class UserFactory extends PersistentObjectFactory
{
    #[Override]
    public static function class(): string
    {
        return User::class;
    }

    #[Override]
    protected function defaults(): array|callable
    {
        return [
            'email' => self::faker()->text(180),
            'password' => self::faker()->text(),
            'roles' => ['ROLE_TECHNICIAN'],
        ];
    }
}
