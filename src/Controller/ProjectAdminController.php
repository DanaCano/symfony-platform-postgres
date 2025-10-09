<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Route('/admin/projects', name: 'admin_projects_')]
class ProjectAdminController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(ProjectRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $projects = $repo->findBy([], ['id' => 'DESC']);
        return $this->render('admin/projects/index.html.twig', ['projects' => $projects]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Project $project, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if ($request->isMethod('POST')) {
            $title = trim((string)$request->request->get('title'));
            $desc  = trim((string)$request->request->get('description'));
            if (!$title || !$desc) {
                $this->addFlash('error', 'Titre et description requis.');
            } else {
                $project->setTitle($title)->setDescription($desc);
                $em->flush();
                $this->addFlash('success', 'Projet mis à jour.');
                return $this->redirectToRoute('admin_projects_index');
            }
        }
        return $this->render('admin/projects/edit.html.twig', ['p' => $project]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Project $project, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if (!$this->isCsrfTokenValid('delete_project_admin_'.$project->getId(), (string)$request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('admin_projects_index');
        }
        $em->remove($project);
        $em->flush();
        $this->addFlash('success', 'Projet supprimé.');
        return $this->redirectToRoute('admin_projects_index');
    }
}