<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/me', name: 'app_me_')]
class ProfileController extends AbstractController
{
    #[Route('', name: 'profile')]
    public function profile(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var User $user */
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $name  = trim((string) $request->request->get('name'));
            $email = trim((string) $request->request->get('email'));

            if (!$name || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Nom ou email invalide.');
            } else {
                $user->setName($name)->setEmail(strtolower($email));
                $em->flush();
                $this->addFlash('success', 'Profil mis à jour.');
                return $this->redirectToRoute('app_me_profile');
            }
        }

        return $this->render('profile/edit.html.twig', ['user' => $user]);
    }

    #[Route('/password', name: 'password')]
    public function password(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var User $user */
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $current = (string) $request->request->get('current_password');
            $new1    = (string) $request->request->get('new_password');
            $new2    = (string) $request->request->get('new_password2');

            if (!$hasher->isPasswordValid($user, $current)) {
                $this->addFlash('error', 'Mot de passe actuel incorrect.');
            } elseif (strlen($new1) < 6) {
                $this->addFlash('error', 'Le nouveau mot de passe doit contenir au moins 6 caractères.');
            } elseif ($new1 !== $new2) {
                $this->addFlash('error', 'Les nouveaux mots de passe ne correspondent pas.');
            } else {
                $user->setPassword($hasher->hashPassword($user, $new1));
                $em->flush();
                $this->addFlash('success', 'Mot de passe modifié.');
                return $this->redirectToRoute('app_me_password');
            }
        }

        return $this->render('profile/password.html.twig');
    }
}