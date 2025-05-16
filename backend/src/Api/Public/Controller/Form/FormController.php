<?php declare(strict_types=1);

namespace App\Api\Public\Controller\Form;

use App\Api\Public\Input\Form\FormInitInput;
use App\Api\Public\Input\Form\FormSubscribeInput;
use App\Api\Public\Object\Form\FormListObject;
use App\Api\Public\Object\Form\FormSubscriberObject;
use App\Api\Public\Object\Form\Project\FormProjectObject;
use App\Entity\Type\SubscriberSource;
use App\Entity\Type\SubscriberStatus;
use App\Service\NewsletterList\NewsletterListService;
use App\Service\Project\ProjectService;
use App\Service\Subscriber\Dto\UpdateSubscriberDto;
use App\Service\Subscriber\SubscriberService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class FormController extends AbstractController
{

    use ClockAwareTrait;

    public function __construct(
        private ProjectService $projectService,
        private NewsletterListService $newsletterListService,
        private SubscriberService $subscriberService,
    )
    {
    }

    #[Route('/form/init', methods: 'POST')]
    public function init(#[MapRequestPayload] FormInitInput $input): JsonResponse
    {

        $project = $this->projectService->getProjectByUuid($input->project_uuid);

        if (!$project) {
            throw new UnprocessableEntityHttpException('Project not found');
        }

        $listIds = $input->list_ids;

        if ($listIds !== null) {
            $missingListIds = $this->newsletterListService->getMissingListIdsOfProject(
                $project,
                $listIds
            );
            if ($missingListIds !== null) {
                throw new UnprocessableEntityHttpException("List with id {$missingListIds[0]} not found");
            }

            $lists = $this->newsletterListService->getListsByIds($listIds);
        } else {
            $lists = $this->newsletterListService->getListsOfProject($project);
        }

        return new JsonResponse([
            'project' => new FormProjectObject($project),
            'is_subscribed' => false,
            'lists' => $lists->map(fn ($list) => new FormListObject($list))->toArray(),
        ]);

    }

    #[Route('/form/subscribe', methods: 'POST')]
    public function subscribe(
        #[MapRequestPayload] FormSubscribeInput $input,
        Request $request,
    ): JsonResponse
    {

        $ip = $request->getClientIp();
        $project = $this->projectService->getProjectById($input->project_id);

        if (!$project) {
            throw new UnprocessableEntityHttpException('Project not found');
        }

        $listIds = $input->list_ids;
        $missingListIds = $this->newsletterListService->getMissingListIdsOfProject(
            $project,
            $listIds
        );

        if ($missingListIds !== null) {
            throw new UnprocessableEntityHttpException("List with id {$missingListIds[0]} not found");
        }

        $lists = $this->newsletterListService->getListsByIds($listIds);

        $email = $input->email;
        $subscriber = $this->subscriberService->getSubscriberByEmail($project, $email);

        if ($subscriber) {

            $update = new UpdateSubscriberDto();
            $update->status = SubscriberStatus::SUBSCRIBED;
            $update->lists = $lists;

            $this->subscriberService->updateSubscriber(
                $subscriber,
                $update
            );

        } else {

            $subscriber = $this->subscriberService->createSubscriber(
                $project,
                $email,
                $lists,
                SubscriberStatus::SUBSCRIBED,
                SubscriberSource::FORM,
                $ip,
                $this->now(),
            );

        }

        return new JsonResponse(new FormSubscriberObject($subscriber));
    }

}
