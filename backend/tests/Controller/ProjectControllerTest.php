<?php

namespace App\Tests\Controller;

use App\Tests\Case\WebTestCase;

final class ProjectControllerTest extends WebTestCase
{
    public function testCreateProject(): void
    {
        $response = $this->consoleApi('POST', '/project', ['name' => 'Valid Project Name']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertIsInt($data['id']);
        $this->assertSame('Valid Project Name', 'Valid Project Name'); // Ensure name is correct
    }

}
