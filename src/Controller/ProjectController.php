<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\Project;
use App\Repository\ApplicationRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/project', name: 'app_project_')]
class ProjectController extends AbstractController
{
    #[Route('/new', name: 'new')]
    public function new(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($request->isMethod('POST')) {
            //CSRF
            $csrf = (string) $request->request->get('_token');
            if (!$this->isCsrfTokenValid('project_new', $csrf)) {
                $this->addFlash('error', 'Token CSRF invalide.');
                return $this->redirectToRoute('app_project_new');
            }

            $title = trim((string)$request->request->get('title'));
            $description = trim((string)$request->request->get('description'));

            if (!$title || !$description) {
                $this->addFlash('error', 'Vous devez remplir le titre et la description.');
            } else {
                $project = new Project();
                $project->setTitle($title)
                        ->setDescription($description)
                        ->setOwner($this->getUser());

                $em = $doctrine->getManager();
                $em->persist($project);
                $em->flush();

                $this->addFlash('success', 'Projet créé.');
                return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
            }
        }

        return $this->render('project/new.html.twig');
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\\d+'])]
    public function show(Project $project): Response
    {
        return $this->render('project/show.html.twig', ['project' => $project]);
    }

    #[Route('/{id}/apply', name: 'apply', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function apply(Project $project, Request $request, ManagerRegistry $doctrine, ApplicationRepository $applicationRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $currentUser = $this->getUser();

        if ($project->getOwner() === $currentUser) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'error' => 'No puedes postularte a tu propio proyecto.'], 400);
            }
            $this->addFlash('error', 'Vous ne pouvez pas postuler à votre propre projet.');
            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
        }

        $existing = $applicationRepository->findOneBy(['project' => $project, 'applicant' => $currentUser]);
        if ($existing) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'error' => 'Ya te has postulado a este proyecto.'], 400);
            }
            $this->addFlash('error', 'Vous avez déjà postulé à ce projet.');
            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
        }

        $message = trim((string)$request->request->get('message'));
        if (!$message) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'error' => 'Le message ne peut pas être vide.'], 400);
            }
            $this->addFlash('error', 'Vous devez rédiger un message pour postuler.');
            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
        }

        $application = new Application();
        $application->setProject($project)->setApplicant($currentUser)->setMessage($message);

        $em = $doctrine->getManager();
        $em->persist($application);
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return $this->json(['success' => true]);
        }

        $this->addFlash('success', 'Candidature envoyée');
        return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
    }

    #[Route('/my-projects', name: 'my_projects')]
    public function myProjects(ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $projects = $doctrine->getRepository(Project::class)->findBy(['owner' => $this->getUser()], ['createdAt' => 'DESC']);
        return $this->render('project/my_projects.html.twig', ['projects' => $projects]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\\d+'])]
    public function edit(Project $project, Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $isOwner = $project->getOwner() === $this->getUser();
        if (!$isOwner && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            //CSRF
            $csrf = (string) $request->request->get('_token');
            if (!$this->isCsrfTokenValid('project_edit_' . $project->getId(), $csrf)) {
                $this->addFlash('error', 'Token CSRF invalide.');
                return $this->redirectToRoute('app_project_edit', ['id' => $project->getId()]);
            }

            $title = trim((string)$request->request->get('title'));
            $description = trim((string)$request->request->get('description'));

            if (!$title || !$description) {
                $this->addFlash('error', 'Vous devez remplir le titre et la description.');
            } else {
                $project->setTitle($title)->setDescription($description);
                $doctrine->getManager()->flush();
                $this->addFlash('success', 'Projet mis à jour.');
                return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
            }
        }

        return $this->render('project/edit.html.twig', ['project' => $project]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function delete(Project $project, Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $isOwner = $project->getOwner() === $this->getUser();
        if (!$isOwner && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('delete_project_'.$project->getId(), (string)$request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
        }

        $em = $doctrine->getManager();
        $em->remove($project);
        $em->flush();

        $this->addFlash('success', 'Projet supprimé.');
        return $this->redirectToRoute('app_home');
    }
}