<?php

declare(strict_types=1);

namespace App\Tests\Api\Ticket;

use App\ServiceRequest\Enum\TicketPriority;
use App\ServiceRequest\Enum\TicketStatus;
use App\Tests\Api\AbstractApiTestCase;
use App\Tests\Api\Factory\DeviceFactory;
use App\Tests\Api\Factory\TicketFactory;
use DateTimeImmutable;

final class GetTicketCollectionTest extends AbstractApiTestCase
{
    public function test_it_returns_paginated_collection_of_tickets(): void
    {
        TicketFactory::createMany(15);
        $client = $this->createAuthenticatedClient();

        $response = $client->request('GET', '/api/tickets?page=2&itemsPerPage=10');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');

        $this->assertJsonContains([
            'meta' => [
                'totalItems' => 15,
                'currentPage' => 2,
            ]
        ]);

        $data = $response->toArray()['data'];
        $this->assertCount(5, $data);
    }

    public function test_it_filters_tickets_by_status(): void
    {
        TicketFactory::createMany(3, ['status' => TicketStatus::NEW]);
        TicketFactory::createMany(2, ['status' => TicketStatus::IN_PROGRESS]);

        $client = $this->createAuthenticatedClient();
        $response = $client->request('GET', '/api/tickets?status=' . TicketStatus::NEW->value);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'meta' => [
                'totalItems' => 3,
            ]
        ]);

        $data = $response->toArray()['data'];
        $this->assertCount(3, $data);
        $this->assertSame(TicketStatus::NEW->value, $data[0]['status']);
    }

    public function test_it_filters_tickets_by_priority(): void
    {
        TicketFactory::createMany(1, ['priority' => TicketPriority::CRITICAL]);
        TicketFactory::createMany(4, ['priority' => TicketPriority::LOW]);

        $client = $this->createAuthenticatedClient();
        $response = $client->request('GET', '/api/tickets?priority=' . TicketPriority::CRITICAL->value);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'meta' => [
                'totalItems' => 1,
            ]
        ]);

        $data = $response->toArray()['data'];
        $this->assertCount(1, $data);
        $this->assertSame(TicketPriority::CRITICAL->value, $data[0]['priority']);
    }

    public function test_it_filters_tickets_by_device_serial_number(): void
    {
        $deviceMatch = DeviceFactory::createOne(['serialNumber' => 'ABC-123-XYZ']);
        $deviceOther = DeviceFactory::createOne(['serialNumber' => 'QWERTY']);

        TicketFactory::createOne(['device' => $deviceMatch, 'title' => 'Ticket with matching device']);
        TicketFactory::createOne(['device' => $deviceOther, 'title' => 'Ticket with other device']);

        $client = $this->createAuthenticatedClient();
        $response = $client->request('GET', '/api/tickets?serialNumber=123');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'meta' => [
                'totalItems' => 1,
            ]
        ]);

        $data = $response->toArray()['data'];
        $this->assertCount(1, $data);
        $this->assertSame('Ticket with matching device', $data[0]['title']);
    }

    public function test_it_sorts_tickets_by_created_at_asc(): void
    {
        TicketFactory::createOne([
            'createdAt' => new DateTimeImmutable('-2 days'),
            'title' => 'Older ticket'
        ]);

        TicketFactory::createOne([
            'createdAt' => new DateTimeImmutable('-1 days'),
            'title' => 'Newer ticket'
        ]);

        $client = $this->createAuthenticatedClient();
        $response = $client->request('GET', '/api/tickets?order[createdAt]=ASC');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray()['data'];
        $this->assertCount(2, $data);
        $this->assertSame('Older ticket', $data[0]['title']);
        $this->assertSame('Newer ticket', $data[1]['title']);
    }

    public function test_it_sorts_tickets_by_created_at_desc_by_default(): void
    {
        TicketFactory::createOne([
            'createdAt' => new DateTimeImmutable('-5 days'),
            'title' => 'Oldest ticket'
        ]);

        TicketFactory::createOne([
            'createdAt' => new DateTimeImmutable('now'),
            'title' => 'Latest ticket'
        ]);

        $client = $this->createAuthenticatedClient();
        $response = $client->request('GET', '/api/tickets');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray()['data'];
        $this->assertCount(2, $data);
        $this->assertSame('Latest ticket', $data[0]['title']);
        $this->assertSame('Oldest ticket', $data[1]['title']);
    }
}
