<?php

namespace App\Tests\Api\Public\Form;

use App\Api\Public\Controller\Form\FormController;
use App\Api\Public\Object\Form\FormListObject;
use App\Api\Public\Object\Form\Newsletter\FormNewsletterObject;
use App\Entity\Type\RelayDomainStatus;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\DomainFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\NewsletterFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FormController::class)]
#[CoversClass(FormListObject::class)]
#[CoversClass(FormNewsletterObject::class)]
class FormInitTest extends WebTestCase
{

    public function test_error_when_newsletter_by_uuid_not_found(): void
    {
        $response = $this->publicApi('POST', '/form/init', [
            'newsletter_subdomain' => 'test',
        ], ['Referer' => 'https://post.hyvor.com']);

        $this->assertResponseStatusCodeSame(422, $response);
        $json = $this->getJson();

        $this->assertSame('Newsletter not found', $json['message']);
    }

    public function test_inits_with_all_lists(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list1 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $list2 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $subdomain = $newsletter->getSubdomain();
        $response = $this->publicApi('POST', '/form/init', [
            'newsletter_subdomain' => $subdomain,
        ], ['Referer' => 'https://post.hyvor.com']);

        $this->assertResponseStatusCodeSame(200, $response);
        $json = $this->getJson();

        // newsletter
        $newsletterArray = $json['newsletter'];
        $this->assertIsArray($newsletterArray);
        $this->assertSame($subdomain, $newsletterArray['subdomain']);

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

        $subdomain = $newsletter->getSubdomain();
        $response = $this->publicApi('POST', '/form/init', [
            'newsletter_subdomain' => $subdomain,
            'list_ids' => [$list1->getId()],
        ], ['Referer' => 'https://post.hyvor.com']);

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

        $subdomain = $newsletter->getSubdomain();
        $response = $this->publicApi('POST', '/form/init', [
            'newsletter_subdomain' => $subdomain,
            'list_ids' => [$list1->getId(), $list2->getId()],
        ], ['Referer' => 'https://post.hyvor.com']);

        $this->assertResponseStatusCodeSame(422, $response);
        $json = $this->getJson();

        // error
        $list2Id = $list2->getId();
        $this->assertSame("List with id $list2Id not found", $json['message']);
    }

    // Domain validation tests

    public function test_allows_app_domain_for_preview(): void
    {
        $newsletter = NewsletterFactory::createOne([
            'allowed_domains' => ['example.com'],
        ]);

        $response = $this->publicApi('POST', '/form/init', [
            'newsletter_subdomain' => $newsletter->getSubdomain(),
        ], ['Referer' => 'https://post.hyvor.com/console/1/settings']);

        $this->assertResponseStatusCodeSame(200, $response);
    }

    public function test_allows_exact_domain_match(): void
    {
        $newsletter = NewsletterFactory::createOne([
            'allowed_domains' => ['example.com'],
        ]);

        $response = $this->publicApi('POST', '/form/init', [
            'newsletter_subdomain' => $newsletter->getSubdomain(),
        ], ['Referer' => 'https://example.com/page']);

        $this->assertResponseStatusCodeSame(200, $response);
    }

    public function test_allows_subdomain_of_allowed_domain(): void
    {
        $newsletter = NewsletterFactory::createOne([
            'allowed_domains' => ['example.com'],
        ]);

        $response = $this->publicApi('POST', '/form/init', [
            'newsletter_subdomain' => $newsletter->getSubdomain(),
        ], ['Referer' => 'https://sub.example.com/page']);

        $this->assertResponseStatusCodeSame(200, $response);
    }

    public function test_rejects_domain_not_in_allowed_list(): void
    {
        $newsletter = NewsletterFactory::createOne([
            'allowed_domains' => ['example.com'],
        ]);

        $response = $this->publicApi('POST', '/form/init', [
            'newsletter_subdomain' => $newsletter->getSubdomain(),
        ], ['Referer' => 'https://other.com/page']);

        $this->assertResponseStatusCodeSame(422, $response);
        $json = $this->getJson();
        $this->assertIsString($json['message']);
        $this->assertStringContainsString('not allowed', $json['message']);
        $this->assertStringContainsString('Settings:', $json['message']);
    }

}
