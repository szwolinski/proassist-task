<?php

declare(strict_types=1);

namespace App\Tests\Api\Report;

use App\ServiceRequest\Enum\TicketStatus;
use App\Tests\Api\AbstractApiTestCase;
use App\Tests\Api\Factory\TechnicianFactory;
use App\Tests\Api\Factory\TicketFactory;

final class GetTechnicianPerformanceReportTest extends AbstractApiTestCase
{
    public function test_it_returns_performance_report_for_multiple_technicians_successfully(): void
    {
        $tech1 = TechnicianFactory::createOne([
            'firstName' => 'John',
            'lastName' => 'Doe',
        ]);

        TicketFactory::createMany(3, [
            'assignedTechnician' => $tech1,
            'status' => TicketStatus::DONE,
        ]);

        TicketFactory::createMany(2, [
            'assignedTechnician' => $tech1,
            'status' => TicketStatus::NEW,
        ]);

        $tech2 = TechnicianFactory::createOne([
            'firstName' => 'Alice',
            'lastName' => 'Smith',
        ]);

        TicketFactory::createMany(1, [
            'assignedTechnician' => $tech2,
            'status' => TicketStatus::DONE,
        ]);

        TicketFactory::createMany(3, [
            'assignedTechnician' => $tech2,
            'status' => TicketStatus::NEW,
        ]);

        $tech3 = TechnicianFactory::createOne([
            'firstName' => 'Bob',
            'lastName' => 'Brown',
        ]);

        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/reports/technician-performance');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');

        // Report is grouped by technician id and then sorted in-memory by last name and first name.
        $this->assertJsonContains([
            [
                'technicianId' => $tech3->getId(),
                'technicianName' => 'Bob Brown',
                'totalAssigned' => 0,
                'totalCompleted' => 0,
            ],
            [
                'technicianId' => $tech1->getId(),
                'technicianName' => 'John Doe',
                'totalAssigned' => 5,
                'totalCompleted' => 3,
            ],
            [
                'technicianId' => $tech2->getId(),
                'technicianName' => 'Alice Smith',
                'totalAssigned' => 4,
                'totalCompleted' => 1,
            ],
        ]);
    }

    public function test_it_returns_401_when_anonymous(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/reports/technician-performance');

        $this->assertResponseStatusCodeSame(401);
    }
}
