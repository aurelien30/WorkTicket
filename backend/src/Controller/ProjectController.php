<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProjectController extends AbstractController
{
    #[Route('/projects/{id}/kanban', name: 'project_kanban')]
    #[IsGranted('ROLE_USER')]
    public function kanban(int $id, ProjectRepository $projectRepository, TaskRepository $taskRepository): Response
    {
        $project = $projectRepository->find($id);

        $tasksAFaire = $taskRepository->findBy(['project' => $project, 'status' => 'A_FAIRE']);
        $tasksEnCours = $taskRepository->findBy(['project' => $project, 'status' => 'EN_COURS']);
        $tasksTermine = $taskRepository->findBy(['project' => $project, 'status' => 'TERMINE']);

        return $this->render('project/kanban.html.twig', [
            'project' => $project,
            'tasksAFaire' => $tasksAFaire,
            'tasksEnCours' => $tasksEnCours,
            'tasksTermine' => $tasksTermine,
        ]);
    }
}