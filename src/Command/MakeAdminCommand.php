<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:make-admin', description: 'Concede ROLE_ADMIN a un usuario por email')]
class MakeAdminCommand extends Command
{
    public function __construct(private UserRepository $users, private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Email del usuario');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (string)$input->getArgument('email');
        $user = $this->users->findOneBy(['email' => $email]);
        if (!$user) { $output->writeln('<error>Usuario no encontrado</error>'); return Command::FAILURE; }
        $roles = $user->getRoles();
        if (!in_array('ROLE_ADMIN', $roles)) {
            $roles[] = 'ROLE_ADMIN';
            $user->setRoles($roles);
            $this->em->flush();
            $output->writeln('<info>ROLE_ADMIN asignado.</info>');
        } else {
            $output->writeln('<comment>El usuario ya es admin.</comment>');
        }
        return Command::SUCCESS;
    }
}
