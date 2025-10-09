<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Route('/admin/users', name: 'admin_users_')]
class UserAdminController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(UserRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $users = $repo->findBy([], ['id' => 'DESC']);
        return $this->render('admin/users/index.html.twig', ['users' => $users]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($request->isMethod('POST')) {
            $name  = trim((string)$request->request->get('name'));
            $email = trim((string)$request->request->get('email'));
            $isAdmin = (bool) $request->request->get('is_admin');

            if (!$name || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Nom ou email invalide.');
            } else {
                $user->setName($name)->setEmail(strtolower($email));
                $user->setRoles($isAdmin ? ['ROLE_ADMIN'] : ['ROLE_USER']);
                $em->flush();
                $this->addFlash('success', 'Utilisateur mis à jour.');
                return $this->redirectToRoute('admin_users_index');
            }
        }

        return $this->render('admin/users/edit.html.twig', ['u' => $user]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if (!$this->isCsrfTokenValid('delete_user_'.$user->getId(), (string)$request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('admin_users_index');
        }
        if ($this->getUser() && $user->getId() === $this->getUser()->getId()) {
            $this->addFlash('error', 'Vous ne pouvez pas vous supprimer vous-même.');
            return $this->redirectToRoute('admin_users_index');
        }
        $em->remove($user);
        $em->flush();
        $this->addFlash('success', 'Utilisateur supprimé.');
        return $this->redirectToRoute('admin_users_index');
    }
}
