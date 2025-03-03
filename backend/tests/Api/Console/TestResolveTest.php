<?php

namespace App\Tests\Api\Console;

use App\Tests\Case\WebTestCase;

class TestResolveTest extends WebTestCase
{

    public function testTest(): void
    {

        $data = [
            // 'email' => null
        ];

        $this->client->request(
            'PATCH',
            '/api/test/',
            server: ['HTTP_CONTENT_TYPE' => 'application/json'],
            content: (string) json_encode($data)
        );

        $response = $this->client->getResponse();
        dd($response->getContent());

    }

    public function testTest2(): void
    {

        $notSet = new MyObject();

        $nullSet = new MyObject();
        $nullSet->email = null;

        $set = new MyObject();
        $set->email = 'supun@hyvor.com';

        dump(
            'property_exists',
            [
                'notSet' => property_exists($notSet, 'email'),
                'null' => property_exists($nullSet, 'email'),
                'set' => property_exists($set, 'email'),
            ]
        );

        dump(
            'isset',
            [
                'notSet' => isset($notSet->email),
                'null' => isset($nullSet->email),
                'set' => isset($set->email),
            ]
        );

        dump(
            'reflection',
            [
                'notSet' => $this->checkReflection($notSet, 'email'),
                'null' => $this->checkReflection($nullSet, 'email'),
                'set' => $this->checkReflection($set, 'email'),
            ]
        );

        dump(
            'accessError',
            [
                'notSet' => $this->accessError($notSet, 'email'),
                'null' => $this->accessError($nullSet, 'email'),
                'set' => $this->accessError($set, 'email'),
            ]
        );

        $this->assertTrue(true);

    }

    private function checkReflection(object $object, string $property): bool
    {
        $propertyReflection = new \ReflectionProperty($object, $property);
        return $propertyReflection->isInitialized($object);
    }

    private function accessError(object $object, string $property): bool
    {
        try {
            $value = $object->{$property};
            return true;
        } catch (\Error $e) {
            return false;
        }
    }

}

class MyObject
{
    public ?string $email;
}