<?php

namespace App\Tests\Api\Console\Media;

use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\MediaFactory;

class GetMediaTest extends WebTestCase 
{
    public function test_it_works(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $media = MediaFactory::createMany(5, ['newsletter'=> $newsletter]);

        $response = $this->consoleApi(
            $newsletter,
            'GET',
            '/media'
        );

        $this->assertResponseStatusCodeSame(200);
        // dd($response->getContent());
        $json = $this->getJson();
        dd($json);
        $this->assertSame('hi!', $json['message']);
    }
}