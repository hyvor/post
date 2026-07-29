<?php

declare(strict_types=1);

namespace App\Api\Console\Controller;

use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Bundle\Api\DataCarryingHttpException;
use Hyvor\Internal\CloudApi\Scope\PostScope;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ScopeRequired;
use App\Api\Console\Object\ListObject;
use App\Api\Console\Object\NewsletterListObject;
use App\Api\Console\Object\NewsletterObject;
use App\Api\Console\Object\SendingProfileObject;
use App\Api\Console\Object\SubscriberMetadataDefinitionObject;
use App\Entity\Newsletter;
use App\Repository\ListRepository;
use App\Service\AppConfig;
use App\Service\Newsletter\NewsletterDefaults;
use App\Service\Newsletter\NewsletterService;
use App\Service\NewsletterList\NewsletterListService;
use App\Service\SendingProfile\SendingProfileService;
use App\Service\SubscriberMetadata\SubscriberMetadataService;
use Hyvor\Internal\Billing\BillingInterface;
use Hyvor\Internal\Billing\License\PostLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use Hyvor\Internal\Bundle\Comms\Exception\CommsApiFailedException;
use Hyvor\Internal\InternalConfig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\OrgEndpoint;

class ConsoleController extends AbstractController
{
    public function __construct(
        private NewsletterService $newsletterService,
        private NewsletterListService $listService,
        private ListRepository $listRepository,
        private InternalConfig $internalConfig,
        private AppConfig $appConfig,
        private SubscriberMetadataService $subscriberMetadataService,
        private SendingProfileService $sendingProfileService,
        private BillingInterface $billing,
        private AuthInterface $auth,
    ) {}

    #[Route('/init', methods: 'GET')]
    public function initConsole(Request $request): JsonResponse
    {
        $me = $this->auth->me($request);

        if ($me === null) {
            throw new DataCarryingHttpException(
                401,
                [
                    'login_url' => $this->auth->authUrl('login'),
                    'signup_url' => $this->auth->authUrl('signup'),
                ],
                'Unauthorized',
            );
        }

        $user = $me->getUser();
        $organization = $me->getOrganization();

        $newsletters = [];
        $license = new ResolvedLicense(ResolvedLicenseType::NONE);

        if ($organization) {
            $newsletters = array_map(
                fn(array $pair) => new NewsletterListObject($pair['newsletter'], $pair['user']),
                $this->newsletterService->getUserNewslettersOfOrganization($user->id, $organization->id),
            );
            $license = $this->billing->license($organization->id);
        }

        return new JsonResponse([
            'newsletters' => $newsletters,
            'user' => $user,
            'organization' => $organization,
            'resolved_license' => $license,
            'config' => [
                'hyvor' => [
                    'instance' => $this->internalConfig->getInstance(),
                ],
                'app' => [
                    'default_email_domain' => $this->appConfig->getSystemMailDomain(),
                    'archive_url' => $this->appConfig->getUrlArchive(),
                    'api_keys' => [
                        'scopes' => array_map(fn($scope) => $scope->value, PostScope::cases()),
                    ],
                ],
                'newsletter_defaults' => NewsletterDefaults::getAll(),
            ],
        ]);
    }

    #[Route('/init/newsletter', methods: 'GET')]
    #[ScopeRequired(PostScope::NEWSLETTER_READ)]
    public function initNewsletter(Newsletter $newsletter): JsonResponse
    {
        $newsletterStats = $this->newsletterService->getnewsletterStats($newsletter);
        $lists = $this->listRepository->findBy(
            [
                'newsletter' => $newsletter,
                'deleted_at' => null,
            ],
        );
        $listIds = array_map(fn($list) => $list->getId(), $lists);
        $subscriberCounts = $this->listService->getSubscriberCountOfLists($listIds);

        $subscriberMetadataDefinitions = $this->subscriberMetadataService->getMetadataDefinitions($newsletter);

        $organizationId = $newsletter->getOrganizationId();
        $canChangeBranding = false;

        try {
            $license = $this->billing->license($organizationId)->license;
            if ($license instanceof PostLicense) {
                // can only change branding if no branding is enabled in license
                $canChangeBranding = $license->allowRemoveBranding === true;
            }
        } catch (CommsApiFailedException) {
            $license = null;
        }

        return new JsonResponse([
            'newsletter' => new NewsletterObject($newsletter),
            'lists' => array_map(fn($list) => new ListObject($list, $subscriberCounts[$list->getId()]), $lists),
            'sending_profiles' => array_map(fn($address) => new SendingProfileObject($address),
                $this->sendingProfileService->getSendingProfiles($newsletter)),
            'subscriber_metadata_definitions' => array_map(fn($def) => new SubscriberMetadataDefinitionObject($def),
                $subscriberMetadataDefinitions),
            'stats' => $newsletterStats,
            'permissions' => [
                'can_change_branding' => $canChangeBranding,
            ],
        ]);
    }
}
