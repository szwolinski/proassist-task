<?php

declare(strict_types=1);

namespace App\Tests\Api\Ticket;

use App\ServiceRequest\Enum\TicketPriority;
use App\ServiceRequest\Enum\TicketStatus;
use App\Tests\Api\AbstractApiTestCase;
use App\Tests\Api\Factory\TicketFactory;

final class GetTicketItemTest extends AbstractApiTestCase
{
    public function test_it_returns_a_ticket_successfully(): void
    {
        $ticket = TicketFactory::createOne([
            'title' => 'Network switch is down',
            'description' => 'Main switch in server room B is unresponsive.',
            'priority' => TicketPriority::HIGH,
            'status' => TicketStatus::NEW,
        ]);

        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/tickets/' . $ticket->getId());

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');

        $this->assertJsonContains([
            'id' => $ticket->getId(),
            'title' => 'Network switch is down',
            'description' => 'Main switch in server room B is unresponsive.',
            'status' => TicketStatus::NEW->value,
            'priority' => TicketPriority::HIGH->value,
        ]);
    }

    public function test_it_returns_404_for_missing_ticket(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/tickets/999999');

        $this->assertResponseStatusCodeSame(404);
    }
}
