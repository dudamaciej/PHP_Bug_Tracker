<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RouteSmokeTest extends WebTestCase
{
    public function testHomeRoute(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
    }

    public function testLoginRoute(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
    }

    public function testCategoryIndexRoute(): void
    {
        $client = static::createClient();
        $client->request('GET', '/category/');
        $this->assertTrue(
            in_array($client->getResponse()->getStatusCode(), [200, 302]),
            'Index route should return 200 or 302.'
        );
    }

    public function testCategoryNewRoute(): void
    {
        $client = static::createClient();
        $client->request('GET', '/category/new');
        $this->assertTrue(
            in_array($client->getResponse()->getStatusCode(), [200, 302]),
            'New route should return 200 or 302.'
        );
    }

    public function testIssueIndexRoute(): void
    {
        $client = static::createClient();
        $client->request('GET', '/issue/');
        $this->assertTrue(
            in_array($client->getResponse()->getStatusCode(), [200, 302]),
            'Index route should return 200 or 302.'
        );
    }

    public function testIssueNewRoute(): void
    {
        $client = static::createClient();
        $client->request('GET', '/issue/new');
        $this->assertTrue(
            in_array($client->getResponse()->getStatusCode(), [200, 302]),
            'New route should return 200 or 302.'
        );
    }

    public function testIssueShowRoute(): void
    {
        $client = static::createClient();
        $client->request('GET', '/issue/1');
        $this->assertTrue(
            in_array($client->getResponse()->getStatusCode(), [200, 404]),
            'Show route should return 200 or 404.'
        );
    }

    public function testIssueEditRoute(): void
    {
        $client = static::createClient();
        $client->request('GET', '/issue/1/edit');
        $this->assertTrue(
            in_array($client->getResponse()->getStatusCode(), [200, 302, 404]),
            'Edit route should return 200, 302, or 404.'
        );
    }
} 