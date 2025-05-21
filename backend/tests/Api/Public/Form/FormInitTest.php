<?php

namespace App\Tests\Api\Public\Form;

use App\Api\Public\Controller\Form\FormController;
use App\Api\Public\Object\Form\FormListObject;
use App\Api\Public\Object\Form\Project\FormProjectObject;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Uid\Uuid;

#[CoversClass(FormController::class)]
#[CoversClass(FormListObject::class)]
#[CoversClass(FormProjectObject::class)]
class FormInitTest extends WebTestCase
{

    public function test_error_when_project_by_uuid_not_found(): void
    {

        $response = $this->publicApi('POST', '/form/init', [
            'project_uuid' => Uuid::v4(),
        ]);

        $this->assertResponseStatusCodeSame(422, $response);
        $json = $this->getJson();

        $this->assertSame('Project not found', $json['message']);

    }

    public function test_inits_with_all_lists(): void
    {

        $project = ProjectFactory::createOne();
        $list1 = NewsletterListFactory::createOne(['project' => $project]);
        $list2 = NewsletterListFactory::createOne(['project' => $project]);

        $uuid = $project->getUuid();
        $response = $this->publicApi('POST', '/form/init', [
            'project_uuid' => $uuid,
        ]);

        $this->assertResponseStatusCodeSame(200, $response);
        $json = $this->getJson();

        // project
        $projectArray = $json['project'];
        $this->assertIsArray($projectArray);
        $this->assertSame($uuid, $projectArray['uuid']);

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

        $project = ProjectFactory::createOne();
        $list1 = NewsletterListFactory::createOne(['project' => $project]);
        $list2 = NewsletterListFactory::createOne(['project' => $project]);

        $uuid = $project->getUuid();
        $response = $this->publicApi('POST', '/form/init', [
            'project_uuid' => $uuid,
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

        $project = ProjectFactory::createOne();
        $list1 = NewsletterListFactory::createOne(['project' => $project]);
        $list2 = NewsletterListFactory::createOne(['project' => ProjectFactory::createOne()]);

        $uuid = $project->getUuid();
        $response = $this->publicApi('POST', '/form/init', [
            'project_uuid' => $uuid,
            'list_ids' => [$list1->getId(), $list2->getId()],
        ]);

        $this->assertResponseStatusCodeSame(422, $response);
        $json = $this->getJson();

        // error
        $list2Id = $list2->getId();
        $this->assertSame("List with id $list2Id not found", $json['message']);

    }

}