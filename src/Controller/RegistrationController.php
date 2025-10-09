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
use Symfony\Component\HttpClient\HttpClient;

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

            $minLen = 8;
            $regex  = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{' . $minLen . ',}$/';

            // CSRF
            $csrf = (string) $request->request->get('_token');
            if (!$this->isCsrfTokenValid('register', $csrf)) {
                $this->addFlash('error', 'Token CSRF invalide.');
                return $this->redirectToRoute('app_register');
            }

            // CAPTCHA
            $captchaResponse = (string) $request->request->get('g-recaptcha-response');
            if (!$captchaResponse) {
                $this->addFlash('error', 'Captcha requis.');
                return $this->redirectToRoute('app_register');
            }

            $client = HttpClient::create();
            $verify = $client->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                'body' => [
                    'secret'   => $_ENV['RECAPTCHA_SECRET'] ?? '',
                    'response' => $captchaResponse,
                    'remoteip' => $request->getClientIp(),
                ],
            ]);
            $data = $verify->toArray(false);
            if (empty($data['success'])) {
                $this->addFlash('error', 'Captcha invalide.');
                return $this->redirectToRoute('app_register');
            }

            if (!$name || !$email || !$plain) {
                $this->addFlash('error', 'Tous les champs sont obligatoires.');
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Email invalide.');
            } elseif (strlen($plain) < $minLen || !preg_match($regex, $plain)) {
                $this->addFlash('error', 'Le mot de passe doit comporter au moins '
                    . $minLen . ' caractères et inclure des majuscules, des minuscules, des chiffres et des caractères spéciaux.');
            } elseif ($userRepository->findOneBy(['email' => $email])) {
                $this->addFlash('error', 'Un compte existe déjà avec cette adresse e-mail.');
            } else {
                $u = new User();
                $u->setName($name)->setEmail($email)->setRoles(['ROLE_USER']);
                $u->setPassword($passwordHasher->hashPassword($u, $plain));
                $em = $doctrine->getManager();
                $em->persist($u);
                $em->flush();

                $this->addFlash('success', 'Inscription réussie. Connectez-vous.');
                return $this->redirectToRoute('app_login');
            }
        }
        return $this->render('security/register.html.twig');
    }
}
