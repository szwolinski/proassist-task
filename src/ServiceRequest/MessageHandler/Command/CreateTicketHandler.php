<?php

declare(strict_types=1);

namespace App\ServiceRequest\MessageHandler\Command;

use ApiPlatform\HttpCache\PurgerInterface;
use ApiPlatform\Metadata\Exception\AccessDeniedException;
use App\ServiceRequest\Entity\Ticket;
use App\ServiceRequest\Message\Command\CreateTicketCommand;
use App\ServiceRequest\Repository\DeviceRepository;
use App\ServiceRequest\Security\TicketVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Webmozart\Assert\Assert;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class CreateTicketHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DeviceRepository $deviceRepository,
        private Security $security,
        private PurgerInterface $purger,
    ) {
    }

    public function __invoke(CreateTicketCommand $command): int
    {
        if (!$this->security->isGranted(TicketVoter::CREATE, Ticket::class)) {
            throw new AccessDeniedException('You are not allowed to create tickets.');
        }

        $device = $this->deviceRepository->find($command->deviceId);

        if (!$device) {
            throw new NotFoundHttpException('Device not found.');
        }

        $ticket = new Ticket();
        $ticket->setTitle($command->title);
        $ticket->setDescription($command->description);
        $ticket->setPriority($command->priority);
        $ticket->setDevice($device);

        $this->entityManager->persist($ticket);
        $this->entityManager->flush();

        $this->purger->purge([
            '/api/tickets'
        ]);

        Assert::integer($ticket->getId());

        return $ticket->getId();
    }
}
