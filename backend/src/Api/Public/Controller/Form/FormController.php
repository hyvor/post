<?php declare(strict_types=1);

namespace App\Api\Public\Controller\Form;

use App\Api\Public\Input\Form\FormSubscribeInput;
use App\Service\NewsletterList\NewsletterListService;
use App\Service\Project\ProjectService;
use App\Service\Subscriber\SubscriberService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class FormController extends AbstractController
{

    public function __construct(
        private ProjectService $projectService,
        private NewsletterListService $newsletterListService,
        private SubscriberService $subscriberService,
    )
    {
    }

    #[Route('/form/subscribe', methods: 'POST')]
    public function subscribe(#[MapRequestPayload] FormSubscribeInput $input): Response
    {

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

        $subscriber = $this->subscriberService->getSubscriberByEmail($project, $input->email);

        if ($subscriber) {
            //
        } else {
            //
        }

        return new Response();
    }

}
