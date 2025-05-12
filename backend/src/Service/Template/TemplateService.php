<?php

namespace App\Service\Template;

use App\Entity\Project;
use App\Entity\Template;
use App\Service\Template\Dto\UpdateTemplateDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

class TemplateService
{
    public function __construct(
        private EntityManagerInterface $em,
        private Filesystem $filesystem,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    )
    {
    }

    public function getTemplate(Project $project): ?Template
    {
        return $this->em->getRepository(Template::class)->findOneBy([
            'project' => $project,
        ]);
    }

    public function createTemplate(Project $project): Template
    {
        $defaultTemplate = $this->readDefaultTemplate();
        $templateEntity = new Template()
            ->setProject($project)
            ->setTemplate($defaultTemplate)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($templateEntity);
        $this->em->flush();

        return $templateEntity;
    }

    public function readDefaultTemplate(): string
    {
        $templatePath = $this->projectDir . '/templates/newsletter/default.html.twig';
        return $this->filesystem->readFile($templatePath);
    }

    public function updateTemplate(Template $template, UpdateTemplateDto $updates): Template
    {
        if ($updates->hasProperty('template'))
            $template->setTemplate($updates->template);

        $template->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($template);
        $this->em->flush();

        return $template;
    }
}
