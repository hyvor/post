<?php

namespace App\Tests\Api\Console\Subscriber;

use App\Api\Console\Controller\SubscriberController;
use App\Entity\Subscriber;
use App\Repository\SubscriberRepository;
use App\Service\Subscriber\SubscriberService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SubscriberController::class)]
#[CoversClass(SubscriberService::class)]
#[CoversClass(SubscriberRepository::class)]
#[CoversClass(Subscriber::class)]
class DeleteSubscriberTest extends WebTestCase
{

    // TODO: tests for input validation (when the newsletter is not found)
    // TODO: tests for authentication

    public function testDeleteSubscriberFound(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $newsletterList = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'lists' => [$newsletterList],
        ]);

        $subscriberId = $subscriber->getId();

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/subscribers/' . $subscriber->getId()
        );

        $this->assertSame(200, $response->getStatusCode());

        $repository = $this->em->getRepository(Subscriber::class);
        $subscriber = $repository->find($subscriberId);
        $this->assertNull($subscriber);
    }

    public function testDeleteSubscriberNotFound(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/subscribers/1'
        );

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testCannotDeleteOtherNewsletterSubscriber(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $otherNewsletter = NewsletterFactory::createOne();

        $newsletterList = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'lists' => [$newsletterList],
        ]);

        $response = $this->consoleApi(
            $otherNewsletter,
            'DELETE',
            '/subscribers/' . $subscriber->getId()
        );

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('Entity does not belong to the newsletter', $this->getJson()['message']);
    }
}
