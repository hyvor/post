<?php

namespace App\Api\Public\Controller\Template;

use Symfony\Component\Routing\Attribute\Route;

class TemplateController
{

    #[Route('/template/with', methods: 'GET')]
    public function renderWith(): void
    {
        // render the template
    }

}
