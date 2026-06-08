<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\ServiceRequest\Entity\Device;
use App\ServiceRequest\Entity\Technician;
use App\ServiceRequest\Entity\Ticket;
use App\ServiceRequest\Enum\TicketPriority;
use App\ServiceRequest\Enum\TicketStatus;
use App\User\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
    public function __construct(readonly private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('pl_PL');

        $dispatcher = new User();
        $dispatcher->setEmail('admin@example.com');
        $dispatcher->setRoles(['ROLE_ADMIN']);
        $dispatcher->setPassword($this->passwordHasher->hashPassword($dispatcher, 'password123'));
        $manager->persist($dispatcher);

        $technicians = [];
        for ($i = 0; $i < 5; $i++) {
            $tech = new Technician();
            $tech->setFirstName($faker->firstName());
            $tech->setLastName($faker->lastName());
            $tech->setEmail($faker->unique()->safeEmail());
            $tech->setActive($i === 0 || $faker->boolean(80));
            $manager->persist($tech);
            $technicians[] = $tech;
        }

        $devices = [];
        for ($i = 0; $i < 20; $i++) {
            $device = new Device();
            $device->setSerialNumber(strtoupper($faker->bothify('SN-????-####')));
            $device->setModel($faker->randomElement(['Dell PowerEdge', 'HP ProLiant', 'Cisco Catalyst', 'Lenovo ThinkSystem']) . ' ' . $faker->randomNumber(3));
            $device->setCustomerName($faker->company());
            $manager->persist($device);
            $devices[] = $device;
        }

        for ($i = 0; $i < 15; $i++) {
            $ticket = new Ticket();
            $ticket->setTitle($faker->realText(50));
            $ticket->setDescription($faker->realText());
            $ticket->setPriority($faker->randomElement(TicketPriority::cases()));

            $status = $faker->randomElement(TicketStatus::cases());
            $ticket->setStatus($status);

            $ticket->setDevice($faker->randomElement($devices));

            if (in_array($status, [TicketStatus::IN_PROGRESS, TicketStatus::DONE, TicketStatus::CANCELLED])) {
                $ticket->setAssignedTechnician($faker->randomElement($technicians));
            }

            if ($status === 'DONE' || $status === 'CANCELLED') {
                $ticket->close();
            }

            $manager->persist($ticket);
        }

        $manager->flush();
    }
}
