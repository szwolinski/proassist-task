<?php

declare(strict_types=1);

namespace App\Tests\Api\Ticket;

use App\ServiceRequest\Enum\TicketStatus;
use App\Tests\Api\AbstractApiTestCase;
use App\Tests\Api\Factory\TechnicianFactory;
use App\Tests\Api\Factory\TicketFactory;

final class ChangeTicketStatusTest extends AbstractApiTestCase
{
    public function test_it_changes_status_successfully_as_admin(): void
    {
        $ticket = TicketFactory::createOne([
            'status' => TicketStatus::IN_PROGRESS
        ]);

        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/tickets/' . $ticket->getId() . '/status', [
            'json' => [
                'transition' => 'finish',
                'expectedVersion' => $ticket->getVersion(),
            ]
        ]);

        $this->assertResponseStatusCodeSame(204);
    }

    public function test_it_changes_status_successfully_as_assigned_technician(): void
    {
        $technicianEmail = 'technician@example.com';
        $technician = TechnicianFactory::createOne([
            'email' => $technicianEmail
        ]);

        $ticket = TicketFactory::createOne([
            'status' => TicketStatus::ASSIGNED,
            'assignedTechnician' => $technician
        ]);

        $client = $this->createAuthenticatedClient('ROLE_USER', $technicianEmail);

        $client->request('POST', '/api/tickets/' . $ticket->getId() . '/status', [
            'json' => [
                'transition' => 'start',
                'expectedVersion' => $ticket->getVersion(),
            ]
        ]);

        $this->assertResponseStatusCodeSame(204);
    }

    public function test_it_returns_403_when_user_has_no_permission(): void
    {
        $technician = TechnicianFactory::createOne([
            'email' => 'other_technician@example.com'
        ]);

        $ticket = TicketFactory::createOne([
            'status' => TicketStatus::NEW,
            'assignedTechnician' => $technician
        ]);

        $client = $this->createAuthenticatedClient('ROLE_USER', 'unassigned@example.com');

        $client->request('POST', '/api/tickets/' . $ticket->getId() . '/status', [
            'json' => [
                'transition' => 'start',
                'expectedVersion' => $ticket->getVersion(),
            ]
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function test_it_returns_404_when_ticket_does_not_exist(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/tickets/999999/status', [
            'json' => [
                'transition' => 'start',
                'expectedVersion' => 1,
            ]
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    public function test_it_returns_409_when_version_mismatch_occurs(): void
    {
        $ticket = TicketFactory::createOne([
            'status' => TicketStatus::NEW
        ]);

        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/tickets/' . $ticket->getId() . '/status', [
            'json' => [
                'transition' => 'start',
                'expectedVersion' => $ticket->getVersion() + 1,
            ]
        ]);

        $this->assertResponseStatusCodeSame(409);
    }

    public function test_it_returns_400_when_workflow_transition_is_invalid(): void
    {
        $ticket = TicketFactory::createOne([
            'status' => TicketStatus::DONE
        ]);

        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/tickets/' . $ticket->getId() . '/status', [
            'json' => [
                'transition' => 'start',
                'expectedVersion' => $ticket->getVersion(),
            ]
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_it_returns_422_when_payload_is_invalid(): void
    {
        $ticket = TicketFactory::createOne();

        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/tickets/' . $ticket->getId() . '/status', [
            'json' => [
                'transition' => '',
                'expectedVersion' => -5,
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
    }
}
