<?php

namespace App\Api\Local;

use App\Entity\Issue;
use App\Entity\Newsletter;
use App\Service\Content\ContentService;
use App\Service\Template\HtmlTemplateRenderer;
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
        private HtmlTemplateRenderer $renderer,
        private EntityManagerInterface $em,
        private ContentService $contentService,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {
    }

    #[Route('/template/basic', methods: 'GET')]
    public function basicTemplate(): Response
    {
        $newsletter = $this->em->getRepository(Newsletter::class)->find(1);
        assert($newsletter instanceof Newsletter);

        $subject = 'Introducing Hyvor Post';
        $content = (string)file_get_contents($this->projectDir . '/templates/newsletter/content-styles.html');

        $json = $this->contentService->getJsonFromHtml($content);

        $issue = new Issue();
        $issue->setNewsletter($newsletter);
        $issue->setContent($json);
        $issue->setSubject($subject);
        $html = $this->renderer->renderFromIssue($issue);

        return new Response($html);
    }

}
