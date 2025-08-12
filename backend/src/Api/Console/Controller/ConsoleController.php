<?php

declare(strict_types=1);

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\AuthorizationListener;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Authorization\UserLevelEndpoint;
use App\Api\Console\Object\ListObject;
use App\Api\Console\Object\NewsletterListObject;
use App\Api\Console\Object\NewsletterObject;
use App\Api\Console\Object\SendingProfileObject;
use App\Api\Console\Object\SubscriberMetadataDefinitionObject;
use App\Entity\Newsletter;
use App\Entity\Type\ApprovalStatus;
use App\Repository\ListRepository;
use App\Service\AppConfig;
use App\Service\Approval\ApprovalService;
use App\Service\Newsletter\NewsletterDefaults;
use App\Service\Newsletter\NewsletterService;
use App\Service\SendingProfile\SendingProfileService;
use App\Service\SubscriberMetadata\SubscriberMetadataService;
use Hyvor\Internal\InternalConfig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ConsoleController extends AbstractController
{
    public function __construct(
        private NewsletterService         $newsletterService,
        private ListRepository            $listRepository,
        private InternalConfig            $internalConfig,
        private AppConfig                 $appConfig,
        private SubscriberMetadataService $subscriberMetadataService,
        private SendingProfileService     $sendingProfileService,
        private ApprovalService           $approvalService,
    )
    {
    }

    #[Route('/init', methods: 'GET')]
    #[UserLevelEndpoint]
    public function initConsole(Request $request): JsonResponse
    {
        $user = AuthorizationListener::getUser($request);

        $newslettersUsers = $this->newsletterService->getnewslettersOfUser($user->id);
        $newsletters = array_map(
            fn(array $pair) => new NewsletterListObject($pair['newsletter'], $pair['user']),
            $newslettersUsers
        );
        $userApproval = $this->approvalService->getApprovalOfUser($user);

        return new JsonResponse([
            'newsletters' => $newsletters,
            'config' => [
                'hyvor' => [
                    'instance' => $this->internalConfig->getInstance(),
                ],
                'app' => [
                    'default_email_domain' => $this->appConfig->getDefaultEmailDomain(),
                    'archive_url' => $this->newsletterService->getArchiveUrl($newslettersUsers[0]['newsletter']),
                    'api_keys' => [
                        'scopes' => array_map(fn($scope) => $scope->value, Scope::cases()),
                    ],
                ],
                // 'template_defaults' => TemplateDefaults::getAll(),
                'newsletter_defaults' => NewsletterDefaults::getAll(),
            ],
            'user_approval' => $userApproval ? $userApproval->getStatus() : ApprovalStatus::PENDING,
            // 'license' => $this->billing->license($user->id, null)
        ]);
    }

    #[Route('/init/newsletter', methods: 'GET')]
    #[ScopeRequired(Scope::NEWSLETTER_READ)]
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
