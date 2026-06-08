<?php

declare(strict_types=1);

namespace App\Tests\Api\Ticket;

use App\ServiceRequest\Enum\TicketStatus;
use App\Tests\Api\AbstractApiTestCase;
use App\Tests\Api\Factory\TechnicianFactory;
use App\Tests\Api\Factory\TicketFactory;

final class AssignTicketTest extends AbstractApiTestCase
{
    public function test_it_assigns_technician_successfully(): void
    {
        $ticket = TicketFactory::createOne([
            'status' => TicketStatus::NEW
        ]);
        $technician = TechnicianFactory::createOne([
            'active' => true
        ]);

        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/tickets/' . $ticket->getId() . '/assign', [
            'json' => [
                'technicianId' => $technician->getId(),
                'expectedVersion' => $ticket->getVersion(),
            ]
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertJsonContains([
            'message' => 'Technician assigned successfully.'
        ]);
    }

    public function test_it_returns_403_when_user_is_not_admin(): void
    {
        $ticket = TicketFactory::createOne();
        $technician = TechnicianFactory::createOne([
            'active' => true
        ]);

        $client = $this->createAuthenticatedClient('ROLE_TECHNICIAN');

        $client->request('POST', '/api/tickets/' . $ticket->getId() . '/assign', [
            'json' => [
                'technicianId' => $technician->getId(),
                'expectedVersion' => $ticket->getVersion(),
            ]
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function test_it_returns_404_when_ticket_does_not_exist(): void
    {
        $technician = TechnicianFactory::createOne([
            'active' => true
        ]);

        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/tickets/999999/assign', [
            'json' => [
                'technicianId' => $technician->getId(),
                'expectedVersion' => 1,
            ]
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    public function test_it_returns_409_when_version_mismatch_occurs(): void
    {
        $ticket = TicketFactory::createOne();
        $technician = TechnicianFactory::createOne([
            'active' => true
        ]);

        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/tickets/' . $ticket->getId() . '/assign', [
            'json' => [
                'technicianId' => $technician->getId(),
                'expectedVersion' => $ticket->getVersion() + 1,
            ]
        ]);

        $this->assertResponseStatusCodeSame(409);
    }

    public function test_it_returns_400_when_technician_does_not_exist(): void
    {
        $ticket = TicketFactory::createOne();

        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/tickets/' . $ticket->getId() . '/assign', [
            'json' => [
                'technicianId' => 999999,
                'expectedVersion' => $ticket->getVersion(),
            ]
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_it_returns_400_when_technician_is_inactive(): void
    {
        $ticket = TicketFactory::createOne();
        $technician = TechnicianFactory::createOne([
            'active' => false
        ]);

        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/tickets/' . $ticket->getId() . '/assign', [
            'json' => [
                'technicianId' => $technician->getId(),
                'expectedVersion' => $ticket->getVersion(),
            ]
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_it_returns_400_when_workflow_transition_is_invalid(): void
    {
        $ticket = TicketFactory::createOne([
            'status' => TicketStatus::DONE
        ]);
        $technician = TechnicianFactory::createOne([
            'active' => true
        ]);

        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/tickets/' . $ticket->getId() . '/assign', [
            'json' => [
                'technicianId' => $technician->getId(),
                'expectedVersion' => $ticket->getVersion(),
            ]
        ]);

        $this->assertResponseStatusCodeSame(400);
    }
}
