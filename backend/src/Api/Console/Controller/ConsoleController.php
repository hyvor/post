<?php

declare(strict_types=1);

namespace App\Api\Console\Controller;

use App\Api\Console\Object\ListObject;
use App\Api\Console\Object\NewsletterListObject;
use App\Api\Console\Object\NewsletterObject;
use App\Api\Console\Object\SendingProfileObject;
use App\Api\Console\Object\StatsObject;
use App\Api\Console\Object\SubscriberMetadataDefinitionObject;
use App\Entity\Newsletter;
use App\Repository\ListRepository;
use App\Service\AppConfig;
use App\Service\Newsletter\NewsletterDefaults;
use App\Service\Newsletter\NewsletterService;
use App\Service\SendingEmail\SendingProfileService;
use App\Service\SubscriberMetadata\SubscriberMetadataService;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\InternalConfig;
use Hyvor\Internal\Bundle\Security\HasHyvorUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ConsoleController extends AbstractController
{

    use HasHyvorUser;

    public function __construct(
        private NewsletterService $newsletterService,
        private ListRepository $listRepository,
        private InternalConfig $internalConfig,
        private AppConfig $appConfig,
        private SubscriberMetadataService $subscriberMetadataService,
        private SendingProfileService $sendingProfileService
    ) {
    }

    #[Route('/init', methods: 'GET')]
    public function initConsole(): JsonResponse
    {
        $user = $this->getUser();
        assert($user instanceof AuthUser);

        $newslettersUsers = $this->newsletterService->getnewslettersOfUser($user->id);
        $newsletters = array_map(
            fn(array $pair) => new NewsletterListObject($pair['newsletter'], $pair['user']),
            $newslettersUsers
        );

        return new JsonResponse([
            'newsletters' => $newsletters,
            'config' => [
                'hyvor' => [
                    'instance' => $this->internalConfig->getInstance(),
                ],
                'app' => [
                    'default_email_domain' => $this->appConfig->getDefaultEmailDomain(),
                ],
                // 'template_defaults' => TemplateDefaults::getAll(),
                'newsletter_defaults' => NewsletterDefaults::getAll(),
            ],
        ]);
    }

    #[Route('/init/newsletter', methods: 'GET')]
    public function initNewsletter(Newsletter $newsletter): JsonResponse
    {
        $newsletterStats = $this->newsletterService->getnewsletterStats($newsletter);
        $lists = $this->listRepository->findBy(
            [
                'newsletter' => $newsletter,
                'deleted_at' => null,
            ]
        );

        $subscriberMetadataDefinitions = $this->subscriberMetadataService->getMetadataDefinitions($newsletter);

        return new JsonResponse([
            'newsletter' => new NewsletterObject($newsletter),
            'lists' => array_map(fn($list) => new ListObject($list), $lists),
            'sending_profiles' => array_map(fn($address) => new SendingProfileObject($address), $this->sendingProfileService->getSendingProfiles($newsletter)),
            'subscriber_metadata_definitions' => array_map(fn($def) => new SubscriberMetadataDefinitionObject($def),
                $subscriberMetadataDefinitions),
            'stats' => $newsletterStats
        ]);
    }

}
