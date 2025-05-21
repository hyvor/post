<?php

namespace App\Api\Local;

use App\Entity\Project;
use App\Service\Content\ContentService;
use App\Service\Template\TemplateRenderer;
use App\Service\Template\TemplateVariables;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @codeCoverageIgnore
 */
class TemplateController extends AbstractController
{

    public function __construct(
        private TemplateRenderer $renderer,
        private EntityManagerInterface $em,
        private ContentService $contentService
    ) {
    }

    #[Route('/template/basic', methods: 'GET')]
    public function basicTemplate(): Response
    {
        $project = $this->em->getRepository(Project::class)->find(1);
        assert($project instanceof Project);

        $subject = 'Introducing Hyvor Post';
        $content = <<<HTML
        <h1>
            Introducing Hyvor Post
        </h1>
        <p>
            We are excited to introduce Hyvor Post, a simple newsletter platform. With Hyvor Post, you can collect emails, create newsletters, and send them to your subscribers.
        </p>
        <button>Go to website</button>
        <p>
            Thank you for your understanding.    
        </p>
        HTML;

        $json = $this->contentService->getJsonFromHtml($content);
        $content = $this->contentService->getHtmlFromJson($json);

        $html = $this->renderer->renderFromSubjectAndContent($project, $subject, $content);

        return new Response($html);
    }

}
