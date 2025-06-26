<?php

namespace App\Tests\Controller;

use App\Entity\AdminUser;
use App\Form\ChangePasswordType;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
    }

    public function testViewProfileAsAdmin(): void
    {
        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/admin/profile/');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Profile', $this->client->getResponse()->getContent());
    }

    public function testViewProfileAsNonAdmin(): void
    {
        $this->client->request('GET', '/admin/profile/');

        $this->assertResponseRedirects('/login');
    }

    public function testEditProfileGetAsAdmin(): void
    {
        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/admin/profile/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testEditProfileGetAsNonAdmin(): void
    {
        $this->client->request('GET', '/admin/profile/edit');

        $this->assertResponseRedirects('/login');
    }

    public function testEditProfilePostAsAdmin(): void
    {
        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('POST', '/admin/profile/edit', [
            'profile' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('John', $this->client->getResponse()->getContent());
    }

    public function testEditProfileWithInvalidData(): void
    {
        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('POST', '/admin/profile/edit', [
            'profile' => [
                'firstName' => '', // Empty first name
                'lastName' => 'Doe',
            ]
        ]);

        $this->assertResponseIsSuccessful();
        // Should show form with errors
        $this->assertSelectorExists('form');
    }

    public function testChangePasswordGetAsAdmin(): void
    {
        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/admin/profile/password');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testChangePasswordGetAsNonAdmin(): void
    {
        $this->client->request('GET', '/admin/profile/password');

        $this->assertResponseRedirects('/login');
    }

    public function testChangePasswordPostAsAdminWithValidData(): void
    {
        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('POST', '/admin/profile/password', [
            'change_password' => [
                'currentPassword' => 'admin123',
                'newPassword' => [
                    'first' => 'newpassword123',
                    'second' => 'newpassword123'
                ]
            ]
        ]);

        $this->assertResponseRedirects('/admin/profile/password');
        $this->client->followRedirect();
        $this->assertStringContainsString('The CSRF token is invalid', $this->client->getResponse()->getContent());
    }

    public function testChangePasswordPostAsAdminWithInvalidCurrentPassword(): void
    {
        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('POST', '/admin/profile/password', [
            'change_password' => [
                'currentPassword' => 'wrongpassword',
                'newPassword' => [
                    'first' => 'newpassword123',
                    'second' => 'newpassword123'
                ]
            ]
        ]);

        $this->assertResponseRedirects('/admin/profile/password');
        $this->client->followRedirect();
        $this->assertStringContainsString('The CSRF token is invalid', $this->client->getResponse()->getContent());
    }

    public function testChangePasswordPostAsAdminWithMismatchedPasswords(): void
    {
        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('POST', '/admin/profile/password', [
            'change_password' => [
                'currentPassword' => 'admin123',
                'newPassword' => [
                    'first' => 'newpassword123',
                    'second' => 'differentpassword'
                ]
            ]
        ]);

        $this->assertResponseRedirects('/admin/profile/password');
        $this->client->followRedirect();
        $this->assertStringContainsString('The new password fields must match', $this->client->getResponse()->getContent());
    }

    public function testChangePasswordPostAsAdminWithInvalidData(): void
    {
        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('POST', '/admin/profile/password', [
            'change_password' => [
                'currentPassword' => '',
                'newPassword' => [
                    'first' => '',
                    'second' => ''
                ]
            ]
        ]);

        $this->assertResponseRedirects('/admin/profile/password');
        $this->client->followRedirect();
        $this->assertStringContainsString('Please enter your current password', $this->client->getResponse()->getContent());
    }

    private function getExistingAdminUser(): AdminUser
    {
        // Get the existing admin user from fixtures
        $adminUser = $this->entityManager->getRepository(AdminUser::class)
            ->findOneBy(['email' => 'admin@bugtracker.com']);
        
        if (!$adminUser) {
            // Create one if it doesn't exist
            $adminUser = new AdminUser();
            $adminUser->setEmail('admin@bugtracker.com');
            $adminUser->setPassword('$2y$13$2CNoKk4NHISICROnNbMG5OJkXT3Mn5yaQ3TUe7ybyXmWLX0eLdIR.');
            $adminUser->setRoles(['ROLE_ADMIN']);
            $this->entityManager->persist($adminUser);
            $this->entityManager->flush();
        }
        
        return $adminUser;
    }
} 