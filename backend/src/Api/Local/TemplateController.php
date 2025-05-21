<?php

namespace App\Api\Local;

use App\Entity\Project;
use App\Service\Content\ContentService;
use App\Service\Template\TemplateRenderer;
use App\Service\Template\TemplateVariables;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
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
        private ContentService $contentService,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {
    }

    #[Route('/template/basic', methods: 'GET')]
    public function basicTemplate(): Response
    {
        $project = $this->em->getRepository(Project::class)->find(1);
        assert($project instanceof Project);

        $subject = 'Introducing Hyvor Post';
        $content = (string)file_get_contents($this->projectDir . '/templates/newsletter/content-styles.html');

        $json = $this->contentService->getJsonFromHtml($content);
        $content = $this->contentService->getHtmlFromJson($json);

        $html = $this->renderer->renderFromSubjectAndContent($project, $subject, $content);

        return new Response($html);
    }

}
