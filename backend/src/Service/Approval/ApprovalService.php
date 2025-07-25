<?php

namespace App\Service\Approval;

use App\Entity\Approval;
use App\Entity\Type\ApprovalStatus;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthUser;
use Symfony\Component\Clock\ClockAwareTrait;

class ApprovalService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * @return Approval[]
     */
    public function getApprovals(): array
    {
        return $this->em->getRepository(Approval::class)
            ->findBy([], ['id' => 'DESC']);
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
        int $userId,
        string $companyName,
        string $country,
        string $website,
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

        return $approval;
    }

    public function updateStatusById(
        Approval $approval,
        ApprovalStatus $status,
        ?string $public_note,
        ?string $private_note
    ): Approval
    {
        $approval->setStatus($status);
        $approval->setPublicNote($public_note);
        $approval->setPrivateNote($private_note);
        $this->em->persist($approval);
        $this->em->flush();

        return $approval;
    }
}
