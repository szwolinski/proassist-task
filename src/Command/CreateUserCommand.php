<?php

declare(strict_types=1);

namespace App\Command;

use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create a user with specified role.',
)]
class CreateUserCommand extends Command
{
    private const array AVAILABLE_ROLES = [
        'ROLE_ADMIN',
        'ROLE_TECHNICIAN'
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('password', InputArgument::REQUIRED, 'Plaintext password')
            ->addArgument('role', InputArgument::OPTIONAL, 'User role', 'ROLE_TECHNICIAN', self::AVAILABLE_ROLES);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $plainPassword = $input->getArgument('password');
        $role = strtoupper($input->getArgument('role'));

        if (!str_starts_with($role, 'ROLE_')) {
            $role = 'ROLE_' . $role;
        }

        if (!in_array($role, self::AVAILABLE_ROLES, true)) {
            $io->error(sprintf('Role "%s" is invalid.', $role));
            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRoles([$role]);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('User %s was successfully created with role %s!', $email, $role));

        return Command::SUCCESS;
    }
}
