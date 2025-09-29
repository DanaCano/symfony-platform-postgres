<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, ManagerRegistry $doctrine, UserRepository $userRepository): Response
    {
        if ($this->getUser()) { return $this->redirectToRoute('app_home'); }

        if ($request->isMethod('POST')) {
            $name = trim((string)$request->request->get('name'));
            $email = trim((string)$request->request->get('email'));
            $plain = (string)$request->request->get('password');

            if (!$name || !$email || !$plain) {
                $this->addFlash('error', 'Todos los campos son obligatorios.');
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Email inválido.');
            } elseif ($userRepository->findOneBy(['email' => $email])) {
                $this->addFlash('error', 'Ya existe una cuenta con ese email.');
            } else {
                $u = new User();
                $u->setName($name)->setEmail($email)->setRoles(['ROLE_USER']);
                $u->setPassword($passwordHasher->hashPassword($u, $plain));
                $em = $doctrine->getManager();
                $em->persist($u); $em->flush();
                $this->addFlash('success', 'Registro exitoso. Inicia sesión.');
                return $this->redirectToRoute('app_login');
            }
        }
        return $this->render('security/register.html.twig');
    }
}
