<?php

namespace App\Service\Approval;

use App\Entity\Approval;
use App\Entity\Type\ApprovalStatus;
use App\Service\AppConfig;
use App\Service\Approval\Dto\UpdateApprovalDto;
use App\Service\Approval\Message\CreateApprovalMessage;
use App\Service\SystemMail\SystemNotificationMailService;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Internationalization\StringsFactory;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Twig\Environment;

class ApprovalService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface        $em,
        private AuthInterface                 $auth,
        private readonly Environment          $mailTemplate,
        private readonly StringsFactory       $stringsFactory,
        private SystemNotificationMailService $emailNotificationService,
        private MessageBusInterface           $messageBus,
        private AppConfig                     $appConfig
    )
    {
    }

    /**
     * @return Approval[]
     */
    public function getApprovals(?int $userId = null, ?ApprovalStatus $status = null, int $limit = 50, int $offset = 0): array
    {
        $criteria = [];

        if ($userId !== null) {
            $criteria['user_id'] = $userId;
        }
        if ($status !== null) {
            $criteria['status'] = $status;
        }

        return $this->em->getRepository(Approval::class)
            ->findBy(
                $criteria,
                ['id' => 'DESC'],
                $limit,
                $offset
            );
    }

    public function getReviewingApprovalsCount(): int
    {
        return $this->em->getRepository(Approval::class)
            ->count(['status' => ApprovalStatus::REVIEWING]);
    }

    public function getApporvalById(int $id): ?Approval
    {
        return $this->em->getRepository(Approval::class)
            ->findOneBy(['id' => $id]);
    }

    public function getApprovalOfUser(AuthUser $user): ?Approval
    {
        return $this->em->getRepository(Approval::class)
            ->findOneBy(['user_id' => $user->id]);
    }

    public function getApprovalStatusOfUser(AuthUser $user): ApprovalStatus
    {
        $approval = $this->getApprovalOfUser($user);
        return $approval === null ? ApprovalStatus::PENDING : $approval->getStatus();
    }

    public function createApproval(
        int     $userId,
        string  $companyName,
        string  $country,
        string  $website,
        ?string $socialLinks,
        ?string $typeOfContent,
        ?string $frequency,
        ?string $existingList,
        ?string $sample,
        ?string $whyPost
    ): Approval
    {
        $otherInfo = [];
        if ($typeOfContent) {
            $otherInfo['type_of_content'] = $typeOfContent;
        }
        if ($frequency) {
            $otherInfo['frequency'] = $frequency;
        }
        if ($existingList) {
            $otherInfo['existing_list'] = $existingList;
        }
        if ($sample) {
            $otherInfo['sample'] = $sample;
        }
        if ($whyPost) {
            $otherInfo['why_post'] = $whyPost;
        }

        $approval = new Approval();
        $approval->setUserId($userId)
            ->setStatus(ApprovalStatus::REVIEWING)
            ->setCompanyName($companyName)
            ->setCountry($country)
            ->setWebsite($website)
            ->setSocialLinks($socialLinks ?? null)
            ->setOtherInfo(count($otherInfo) !== 0 ? $otherInfo : null)
            ->setCreatedAt($this->now())
            ->setUpdatedAt($this->now());

        $this->em->persist($approval);
        $this->em->flush();

        $this->messageBus->dispatch(new CreateApprovalMessage($approval->getId()));

        return $approval;
    }

    public function updateApproval(
        Approval          $approval,
        UpdateApprovalDto $updates
    ): Approval
    {
        if ($updates->hasProperty('companyName')) {
            $approval->setCompanyName($updates->companyName);
        }

        if ($updates->hasProperty('country')) {
            $approval->setCountry($updates->country);
        }

        if ($updates->hasProperty('website')) {
            $approval->setWebsite($updates->website);
        }

        if ($updates->hasProperty('socialLinks')) {
            $approval->setSocialLinks($updates->socialLinks);
        }

        if ($updates->hasProperty('status')) {
            $approval->setStatus($updates->status);
        }

        $otherInfo = $approval->getOtherInfo() ?? [];

        if ($updates->hasProperty('typeOfContent')) {
            if ($updates->typeOfContent === null) {
                unset($otherInfo['type_of_content']);
            } else {
                $otherInfo['type_of_content'] = $updates->typeOfContent;
            }
        }

        if ($updates->hasProperty('frequency')) {
            if ($updates->frequency === null) {
                unset($otherInfo['frequency']);
            } else {
                $otherInfo['frequency'] = $updates->frequency;
            }
        }

        if ($updates->hasProperty('existingList')) {
            if ($updates->existingList === null) {
                unset($otherInfo['existing_list']);
            } else {
                $otherInfo['existing_list'] = $updates->existingList;
            }
        }

        if ($updates->hasProperty('sample')) {
            if ($updates->sample === null) {
                unset($otherInfo['sample']);
            } else {
                $otherInfo['sample'] = $updates->sample;
            }
        }

        if ($updates->hasProperty('whyPost')) {
            if ($updates->whyPost === null) {
                unset($otherInfo['why_post']);
            } else {
                $otherInfo['why_post'] = $updates->whyPost;
            }
        }

        $approval->setOtherInfo(count($otherInfo) !== 0 ? $otherInfo : null);

        $approval->setUpdatedAt($this->now());

        $this->em->persist($approval);
        $this->em->flush();

        return $approval;
    }

    public function changeStatus(
        Approval       $approval,
        ApprovalStatus $status,
        ?string        $public_note,
        ?string        $private_note
    ): Approval
    {
        $approval->setStatus($status);
        $approval->setPublicNote($public_note);
        $approval->setPrivateNote($private_note);

        if ($status === ApprovalStatus::APPROVED) {
            $approval->setApprovedAt($this->now());
        }

        if ($status === ApprovalStatus::REJECTED) {
            $approval->setRejectedAt($this->now());
        }

        $user = $this->auth->fromId($approval->getUserId());
        if (!$user) {
            throw new HttpException(422, "User not found");
        }

        $this->sendApprovalMail($approval, $status, $user);

        $this->em->persist($approval);
        $this->em->flush();

        return $approval;
    }

    private function sendApprovalMail(Approval $approval, ApprovalStatus $status, AuthUser $user): void
    {
        $renderContext = [
            'component' => 'post',
        ];

        $strings = $this->stringsFactory->create();
        $subject = $strings->get('mail.approval.subject', ['status' => $status->value]);
        $content = [
            'greeting' => $strings->get('mail.common.greeting', ['name' => $user->name]),
            'subject' => $subject,
            'footerText' => $strings->get('mail.approval.footerText'),
            'regards' => $strings->get('mail.common.regards'),
        ];

        if ($status === ApprovalStatus::APPROVED) {

            $content['body'] = $strings->get('mail.approval.bodyApproved');
            $content['buttonText'] = $strings->get('mail.approval.buttonText');

            $renderContext['buttonUrl'] = $this->appConfig->getUrlApp() . '/console';

        } elseif ($status === ApprovalStatus::REJECTED) {

            $content['body'] = $strings->get('mail.approval.bodyRejected');

            if ($approval->getPublicNote()) {
                $content['reason'] = $strings->get('mail.approval.reason', ['reason' => $approval->getPublicNote()]);
            }
        } else {
            return;
        }

        $renderContext['strings'] = $content;
        $mail = $this->mailTemplate->render('mail/approval.html.twig', $renderContext);

        $this->emailNotificationService->send(
            $user->email,
            $subject,
            $mail
        );
    }
}
