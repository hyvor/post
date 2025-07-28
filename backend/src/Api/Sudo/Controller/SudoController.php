<?php

namespace App\Api\Sudo\Controller;

use Hyvor\Internal\InternalConfig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class SudoController extends AbstractController
{

    public function __construct(
        private InternalConfig $internalConfig
    ) {}

    #[Route('/init', methods: 'GET')]
    public function initSudo(): JsonResponse
    {
        return new JsonResponse([
           'config' => [
               'hyvor' => [
                   'instance' => $this->internalConfig->getInstance()
               ]
           ]
        ]);
    }
}
