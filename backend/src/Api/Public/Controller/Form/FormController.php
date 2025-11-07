<?php

declare(strict_types=1);

namespace App\Api\Public\Controller\Form;

use App\Api\Public\Input\Form\FormInitInput;
use App\Api\Public\Input\Form\FormRenderInput;
use App\Api\Public\Input\Form\FormSubscribeInput;
use App\Api\Public\Object\Form\FormListObject;
use App\Api\Public\Object\Form\FormSubscriberObject;
use App\Api\Public\Object\Form\Newsletter\FormNewsletterObject;
use App\Entity\Type\SubscriberSource;
use App\Entity\Type\SubscriberStatus;
use App\Service\AppConfig;
use App\Service\NewsletterList\NewsletterListService;
use App\Service\Newsletter\NewsletterService;
use App\Service\Subscriber\Dto\UpdateSubscriberDto;
use App\Service\Subscriber\SubscriberService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class FormController extends AbstractController
{

    use ClockAwareTrait;

    public function __construct(
        private NewsletterService     $newsletterService,
        private NewsletterListService $newsletterListService,
        private SubscriberService     $subscriberService,
        private AppConfig             $appConfig,
    )
    {
    }

    #[Route('/form/init', methods: 'POST')]
    public function init(#[MapRequestPayload] FormInitInput $input): JsonResponse
    {
        $newsletter = $this->newsletterService->getNewsletterBySubdomain($input->newsletter_subdomain);

        if (!$newsletter) {
            throw new UnprocessableEntityHttpException('Newsletter not found');
        }

        $listIds = $input->list_ids;

        if ($listIds !== null) {
            $missingListIds = $this->newsletterListService->getMissingListIdsOfNewsletter(
                $newsletter,
                $listIds
            );
            if ($missingListIds !== null) {
                throw new UnprocessableEntityHttpException("List with id {$missingListIds[0]} not found");
            }

            $lists = $this->newsletterListService->getListsByIds($listIds);
        } else {
            $lists = $this->newsletterListService->getListsOfNewsletter($newsletter);
        }

        return new JsonResponse([
            'newsletter' => new FormNewsletterObject($newsletter),
            'is_subscribed' => false,
            'lists' => $lists->map(fn($list) => new FormListObject($list))->toArray(),
        ]);
    }

    #[Route('/form/subscribe', methods: 'POST')]
    public function subscribe(
        #[MapRequestPayload] FormSubscribeInput $input,
        Request                                 $request,
    ): JsonResponse
    {
        $ip = $request->getClientIp();
        $newsletter = $this->newsletterService->getNewsletterBySubdomain($input->newsletter_subdomain);

        if (!$newsletter) {
            throw new UnprocessableEntityHttpException('Newsletter not found');
        }

        $listIds = $input->list_ids;
        $missingListIds = $this->newsletterListService->getMissingListIdsOfNewsletter(
            $newsletter,
            $listIds
        );

        if ($missingListIds !== null) {
            throw new UnprocessableEntityHttpException("List with id {$missingListIds[0]} not found");
        }

        $lists = $this->newsletterListService->getListsByIds($listIds);

        $email = $input->email;
        $subscriber = $this->subscriberService->getSubscriberByEmail($newsletter, $email);

        if ($subscriber) {
            $update = new UpdateSubscriberDto();
            $update->status = $subscriber->getOptInAt() !== null ? SubscriberStatus::SUBSCRIBED : SubscriberStatus::PENDING;
            $update->lists = $lists;

            $this->subscriberService->updateSubscriber(
                $subscriber,
                $update
            );
        } else {
            $subscriber = $this->subscriberService->createSubscriber(
                $newsletter,
                $email,
                $lists,
                SubscriberStatus::PENDING,
                SubscriberSource::FORM,
                $ip
            );
        }

        return new JsonResponse(new FormSubscriberObject($subscriber));
    }

    #[Route('/form/render', methods: 'GET')]
    public function renderForm(Request $request): Response
    {
        $id = $request->query->get('id');

        $instance = $request->query->get('instance', $this->appConfig->getUrlApp());

        $newsletter = $this->newsletterService->getNewsletterById(intval($id));

        if (!$newsletter) {
            throw new UnprocessableEntityHttpException('Newsletter not found');
        }

        $response = <<<HTML
            <hyvor-post-form newsletter={$newsletter->getSubdomain()}
            instance={$instance}></hyvor-post-form>
            <script type="module" src="{$instance}/form/embed.js"></script>
        HTML;

        return new Response($response);
    }

}
