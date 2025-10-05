<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:make-admin', description: 'Create or promote an admin user')]
class MakeAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Admin email')
            ->addArgument('password', InputArgument::REQUIRED, 'Admin plain password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = strtolower(trim((string) $input->getArgument('email')));
        $plain = (string) $input->getArgument('password');

        /** @var \App\Repository\UserRepository $repo */
        $repo = $this->em->getRepository(User::class);
        $user = $repo->findOneBy(['email' => $email]) ?? new User();

        $user->setEmail($email);
        if (method_exists($user, 'getName') && $user->getName() === '') {
            $user->setName('Admin');
        }

        // rol admin (hereda ROLE_USER por role_hierarchy)
        $user->setRoles(['ROLE_ADMIN']);

        // siempre (re)hasheamos con el algoritmo actual
        $user->setPassword($this->hasher->hashPassword($user, $plain));

        $this->em->persist($user);
        $this->em->flush();

        $io->success(sprintf('Admin listo: %s', $email));
        return Command::SUCCESS;
    }
}