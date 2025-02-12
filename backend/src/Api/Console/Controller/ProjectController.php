<?php

namespace App\Api\Console\Controller;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ProjectController extends AbstractController
{
    #[Route('/project', name: 'create_project', methods: ['POST'])]
    public function createProject(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        $project = new Project();
        $project->setName($data['name']);
        $project->setUserId(1); # TODO: Replace with actual user ID
        $project->setCreatedAt(new \DateTimeImmutable());
        $project->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->persist($project);
        $entityManager->flush();

        return $this->json(['id' => $project->getId()]);
    }
}
