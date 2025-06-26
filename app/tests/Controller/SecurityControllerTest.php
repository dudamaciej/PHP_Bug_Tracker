<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testLoginPage(): void
    {
        $this->client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
    }

    public function testLoginWithValidCredentials(): void
    {
        $this->client->request('GET', '/login');

        $this->client->submitForm('Sign in', [
            '_username' => 'admin@bugtracker.com',
            '_password' => 'admin123',
        ]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $this->client->request('GET', '/login');

        $this->client->submitForm('Sign in', [
            '_username' => 'invalid@example.com',
            '_password' => 'wrongpassword',
        ]);

        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-danger');
    }

    public function testLoginWithEmptyCredentials(): void
    {
        $this->client->request('GET', '/login');

        $this->client->submitForm('Sign in', [
            '_username' => '',
            '_password' => '',
        ]);

        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-danger');
    }

    public function testLogout(): void
    {
        $this->client->request('GET', '/logout');

        // The logout route should redirect or throw an exception
        $this->assertResponseRedirects();
    }

    public function testLoginPageAfterSuccessfulLogin(): void
    {
        // First login
        $this->client->request('GET', '/login');

        $this->client->submitForm('Sign in', [
            '_username' => 'admin@bugtracker.com',
            '_password' => 'admin123',
        ]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        // Try to access login page again when already logged in
        $this->client->request('GET', '/login');

        // Should show the login page (no redirect for logged in users in this app)
        $this->assertResponseIsSuccessful();
    }

    public function testLoginFormSubmissionWithValidCredentials(): void
    {
        $this->client->request('GET', '/login');

        $this->client->submitForm('Sign in', [
            '_username' => 'admin@bugtracker.com',
            '_password' => 'admin123',
        ]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }
}
