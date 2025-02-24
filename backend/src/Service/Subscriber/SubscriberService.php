<?php

namespace App\Service\Subscriber;

use App\Entity\NewsletterList;
use App\Entity\Project;
use App\Entity\Subscriber;
use Doctrine\ORM\EntityManagerInterface;

class SubscriberService
{

    public function __construct(
        private EntityManagerInterface $em
    )
    {
    }

    /**
     * @param array<NewsletterList> $lists
     */
    public function createSubscriber(Project $project, string $email, array $lists): Subscriber
    {
        $subscriber = new Subscriber()
            ->setProject($project)
            ->setEmail($email)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());

        foreach ($lists as $list) {
            $subscriber->addList($list);
        }

        $this->em->persist($subscriber);
        $this->em->flush();

        return $subscriber;
    }
}
