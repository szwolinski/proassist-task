<?php

declare(strict_types=1);

namespace App\Tests\Api\TicketHistory;

use App\ServiceRequest\Enum\TicketStatus;
use App\Tests\Api\AbstractApiTestCase;
use App\Tests\Api\Factory\TicketFactory;
use App\Tests\Api\Factory\TicketHistoryFactory;

final class GetTicketHistoryTest extends AbstractApiTestCase
{
    public function test_it_returns_ticket_history_successfully(): void
    {
        $ticket = TicketFactory::createOne();

        TicketHistoryFactory::createOne([
            'ticket' => $ticket,
            'oldStatus' => TicketStatus::NEW,
            'newStatus' => TicketStatus::IN_PROGRESS,
            'changedBy' => 'admin@example.com',
        ]);

        $client = $this->createAuthenticatedClient();

        $response = $client->request('GET', '/api/tickets/' . $ticket->getId() . '/history');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');

        $data = $response->toArray();
        $this->assertCount(1, $data);

        $this->assertJsonContains([
            [
                'ticketId' => $ticket->getId(),
                'oldStatus' => TicketStatus::NEW->value,
                'newStatus' => TicketStatus::IN_PROGRESS->value,
                'changedBy' => 'admin@example.com',
            ]
        ]);
    }

    public function test_it_returns_empty_array_when_ticket_has_no_history(): void
    {
        $ticket = TicketFactory::createOne();

        $client = $this->createAuthenticatedClient();

        $response = $client->request('GET', '/api/tickets/' . $ticket->getId() . '/history');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();
        $this->assertCount(0, $data);
        $this->assertSame([], $data);
    }

    public function test_it_returns_only_history_belonging_to_the_requested_ticket(): void
    {
        $ticketTarget = TicketFactory::createOne();
        $ticketOther = TicketFactory::createOne();

        TicketHistoryFactory::createOne([
            'ticket' => $ticketTarget,
            'oldStatus' => TicketStatus::NEW,
            'newStatus' => TicketStatus::IN_PROGRESS,
            'changedBy' => 'admin@example.com',
        ]);

        TicketHistoryFactory::createOne([
            'ticket' => $ticketOther,
            'oldStatus' => TicketStatus::NEW,
            'newStatus' => TicketStatus::DONE,
            'changedBy' => 'admin@example.com',
        ]);

        $client = $this->createAuthenticatedClient();

        $response = $client->request('GET', '/api/tickets/' . $ticketTarget->getId() . '/history');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertCount(1, $data);
        $this->assertSame($ticketTarget->getId(), $data[0]['ticketId']);
        $this->assertSame(TicketStatus::IN_PROGRESS->value, $data[0]['newStatus']);
    }

    public function test_it_returns_404_when_ticket_does_not_exist(): void
    {
        $client = $this->createAuthenticatedClient();

        $nonExistentTicketId = 999999;

        $client->request('GET', '/api/tickets/' . $nonExistentTicketId . '/history');

        $this->assertResponseStatusCodeSame(404);
    }
}
