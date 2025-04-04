<?php

namespace App\Api\Public\Controller\Form;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FormController
{

    #[Route('/form/subscribe', methods: 'POST')]
    public function subscribe(): Response
    {
        //

        return new Response();
    }

}
