<?php

namespace App\Tests\Api\Public\Form;

use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;

class FormSubscribeTest extends WebTestCase
{

    public function test_rate_limiter_is_applied(): void
    {
        $response = $this->publicApi('POST', '/form/subscribe');

        $this->assertResponseStatusCodeSame(422, $response);
        $this->assertResponseHeaderSame('Ratelimit-Limit', '30');
        $this->assertResponseHeaderSame('Ratelimit-Remaining', '29');
        $this->assertResponseHeaderSame('Ratelimit-Reset', '0');
    }

    public function test_error_on_missing_project(): void
    {
        $response = $this->publicApi('POST', '/form/subscribe', [
            'project_id' => 1,
            'email' => 'test@hyvor.com',
            'list_ids' => [1],
        ]);

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson($response);

        $this->assertSame('Project not found', $json['message']);
    }

    public function test_subscribes_email(): void
    {

        $project = ProjectFactory::createOne();

        $list1 = NewsletterListFactory::createOne(['project' => $project]);
        $list2 = NewsletterListFactory::createOne(['project' => $project]);

        $response = $this->publicApi('POST', '/form/subscribe', [
            'project_id' => $project->getId(),
            'email' => 'supun@hyvor.com',
            'list_ids' => [
                $list1->getId(),
                $list2->getId(),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200, $response);


    }

}
