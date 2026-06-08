<?php

declare(strict_types=1);

namespace App\Tests\Api\Ticket;

use App\ServiceRequest\Enum\TicketPriority;
use App\Tests\Api\AbstractApiTestCase;
use App\Tests\Api\Factory\DeviceFactory;

final class CreateTicketTest extends AbstractApiTestCase
{
    public function test_it_creates_ticket_successfully(): void
    {
        $device = DeviceFactory::createOne();
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/tickets', [
            'json' => [
                'title' => 'Server power supply failure',
                'description' => 'The device does not turn on after a storm, smells like burning.',
                'priority' => TicketPriority::CRITICAL->value,
                'deviceId' => $device->getId(),
            ]
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');

        $this->assertJsonContains([
            'title' => 'Server power supply failure',
            'status' => 'NEW',
        ]);
    }

    public function test_it_returns_404_for_missing_device(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/tickets', [
            'json' => [
                'title' => 'Server power supply failure',
                'description' => 'The device does not turn on after a storm, smells like burning.',
                'priority' => TicketPriority::CRITICAL->value,
                'deviceId' => 11111,
            ]
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    public function test_it_returns_400_with_invalid_priority(): void
    {
        $client = $this->createAuthenticatedClient();
        $device = DeviceFactory::createOne();

        $client->request('POST', '/api/tickets', [
            'json' => [
                'title' => 'Server power supply failure',
                'description' => 'The device does not turn on after a storm, smells like burning.',
                'priority' => 'INVALID',
                'deviceId' => $device->getId(),
            ]
        ]);

        $this->assertResponseStatusCodeSame(400);
    }
}
