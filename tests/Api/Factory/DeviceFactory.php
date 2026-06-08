<?php

namespace App\Tests\Api\Factory;

use App\ServiceRequest\Entity\Device;
use Override;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;


/**
 * @extends PersistentObjectFactory<Device>
 */
final class DeviceFactory extends PersistentObjectFactory
{
    #[Override]
    public static function class(): string
    {
        return Device::class;
    }

    #[Override]
    protected function defaults(): array|callable
    {
        return [
            'customerName' => self::faker()->text(255),
            'model' => self::faker()->text(255),
            'serialNumber' => self::faker()->text(255),
        ];
    }
}
