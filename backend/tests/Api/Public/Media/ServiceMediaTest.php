<?php

namespace App\Tests\Api\Public\Media;

use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ProjectFactory;
use Symfony\Component\Uid\Uuid;

class ServiceMediaTest extends WebTestCase
{

    public function test_fails_when_project_not_found(): void
    {
        $uuid = Uuid::v4();
        $this->publicApi(
            'GET',
            '/media/' . $uuid . '/1234',
        );

        $this->assertResponseStatusCodeSame(404);
        $json = $this->getJson();
        $this->assertSame('Project not found', $json['message']);
    }

    public function test_when_media_not_found(): void
    {
        $project = ProjectFactory::createOne();
        $this->publicApi(
            'GET',
            '/media/' . $project->getUuid() . '/1234',
        );

        $this->assertResponseStatusCodeSame(404);
        $json = $this->getJson();
        $this->assertSame('Media not found', $json['message']);
    }

    public function test_when_private(): void
    {
    }

}