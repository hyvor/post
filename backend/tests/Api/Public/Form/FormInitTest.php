<?php

namespace App\Tests\Api\Public\Form;

use App\Api\Public\Controller\Form\FormController;
use App\Api\Public\Object\Form\FormListObject;
use App\Api\Public\Object\Form\Newsletter\FormNewsletterObject;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\NewsletterFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Uid\Uuid;

#[CoversClass(FormController::class)]
#[CoversClass(FormListObject::class)]
#[CoversClass(FormNewsletterObject::class)]
class FormInitTest extends WebTestCase
{

    public function test_error_when_newsletter_by_uuid_not_found(): void
    {
        $response = $this->publicApi('POST', '/form/init', [
            'newsletter_uuid' => Uuid::v4(),
        ]);

        $this->assertResponseStatusCodeSame(422, $response);
        $json = $this->getJson();

        $this->assertSame('Newsletter not found', $json['message']);
    }

    public function test_inits_with_all_lists(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list1 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $list2 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $uuid = $newsletter->getUuid();
        $response = $this->publicApi('POST', '/form/init', [
            'newsletter_uuid' => $uuid,
        ]);

        $this->assertResponseStatusCodeSame(200, $response);
        $json = $this->getJson();

        // newsletter
        $newsletterArray = $json['newsletter'];
        $this->assertIsArray($newsletterArray);
        $this->assertSame($uuid, $newsletterArray['uuid']);

        // lists
        $lists = $json['lists'];
        $this->assertIsArray($lists);
        $this->assertCount(2, $lists);
        $this->assertIsArray($lists[0]);
        $this->assertSame($list1->getId(), $lists[0]['id']);
        $this->assertSame($list2->getId(), $lists[1]['id']);
    }

    public function test_inits_with_given_lists(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list1 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $list2 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $uuid = $newsletter->getUuid();
        $response = $this->publicApi('POST', '/form/init', [
            'newsletter_uuid' => $uuid,
            'list_ids' => [$list1->getId()],
        ]);

        $this->assertResponseStatusCodeSame(200, $response);
        $json = $this->getJson();

        // lists
        $lists = $json['lists'];
        $this->assertIsArray($lists);
        $this->assertCount(1, $lists);
        $this->assertIsArray($lists[0]);
        $this->assertSame($list1->getId(), $lists[0]['id']);
    }

    public function test_error_on_invalid_list_id(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list1 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $list2 = NewsletterListFactory::createOne(['newsletter' => NewsletterFactory::createOne()]);

        $uuid = $newsletter->getUuid();
        $response = $this->publicApi('POST', '/form/init', [
            'newsletter_uuid' => $uuid,
            'list_ids' => [$list1->getId(), $list2->getId()],
        ]);

        $this->assertResponseStatusCodeSame(422, $response);
        $json = $this->getJson();

        // error
        $list2Id = $list2->getId();
        $this->assertSame("List with id $list2Id not found", $json['message']);
    }

}