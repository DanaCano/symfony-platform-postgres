<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/dashboard.html.twig');
    }

    #[Route('/after-login', name: 'app_after_login')]
    public function afterLogin(): Response
    {
        // Si no hay usuario autenticado, a la home (o login)
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_dashboard');
        }

        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('user_dashboard'); // usuarios normales
        }

        return $this->redirectToRoute('app_home');
    }
}