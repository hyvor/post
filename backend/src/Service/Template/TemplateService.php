<?php

namespace App\Service\Template;

use App\Entity\Newsletter;
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
    ) {
    }

    public function getTemplate(Newsletter $newsletter): ?Template
    {
        return $this->em->getRepository(Template::class)->findOneBy([
            'newsletter' => $newsletter,
        ]);
    }

    public function createTemplate(Newsletter $newsletter, string $template): Template
    {
        $templateEntity = new Template()
            ->setNewsletter($newsletter)
            ->setTemplate($template)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($templateEntity);
        $this->em->flush();

        return $templateEntity;
    }

    public function getTemplateStringFromNewsletter(Newsletter $newsletter): string
    {
        $template = $this->getTemplate($newsletter);

        if ($template) {
            return $template->getTemplate();
        }

        return $this->readDefaultTemplate();
    }

    public function readDefaultTemplate(): string
    {
        $templatePath = $this->projectDir . '/templates/newsletter/default.html.twig';
        return $this->filesystem->readFile($templatePath);
    }

    public function updateTemplate(Template $template, UpdateTemplateDto $updates): Template
    {
        if ($updates->hasProperty('template')) {
            $template->setTemplate($updates->template);
        }

        $template->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($template);
        $this->em->flush();

        return $template;
    }
}
