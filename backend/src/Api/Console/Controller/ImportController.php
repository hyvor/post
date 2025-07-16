<?php

namespace App\Api\Console\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ImportController extends AbstractController
{
    public function __construct(
    ) {
    }

    #[Route('/subscribers/import/prepare', methods: 'POST')]
    public function prepareImport(): JsonResponse
    {
        // TODO
        return new JsonResponse('Prepare csv for import');
    }

    #[Route('/subscribers/import', methods: 'POST')]
    public function import(): JsonResponse
    {
        // TODO
        return new JsonResponse('Import subscribers from csv');
    }

    #[Route('/subscribers/import', methods: 'GET')]
    public function listImports(): JsonResponse
    {
        // TODO
        return new JsonResponse('List imports');
    }
}
