<?php

namespace App\Service\Template;

use App\Entity\Project;
use App\Entity\Template;
use Doctrine\ORM\EntityManagerInterface;

class TemplateService
{
    public function __construct(
        private EntityManagerInterface $em,
    )
    {
    }

    public function getTemplate(Project $project): ?Template
    {
        $template = $this->em->getRepository(Template::class)->findOneBy([
            'project' => $project,
        ]);

        return $template;
    }

    public function createTemplate(Project $project, string $template): Template
    {
        $templateEntity = new Template()
            ->setProject($project)
            ->setTemplate($template)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($templateEntity);
        $this->em->flush();

        return $templateEntity;
    }
}
