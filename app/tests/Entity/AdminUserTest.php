<?php

namespace App\Tests\Entity;

use App\Entity\AdminUser;
use PHPUnit\Framework\TestCase;

class AdminUserTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $user = new AdminUser();
        $user->setEmail('admin@example.com');
        $user->setPassword('secret');
        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $user->setFirstName('John');
        $user->setLastName('Doe');

        $this->assertEquals('admin@example.com', $user->getEmail());
        $this->assertEquals('secret', $user->getPassword());
        $this->assertEquals(['ROLE_ADMIN', 'ROLE_USER'], $user->getRoles());
        $this->assertEquals('John', $user->getFirstName());
        $this->assertEquals('Doe', $user->getLastName());
    }

    public function testDefaultRoles(): void
    {
        $user = new AdminUser();
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
    }

    public function testEraseCredentials(): void
    {
        $user = new AdminUser();
        $this->assertNull($user->eraseCredentials());
    }

    public function testUserIdentifier(): void
    {
        $user = new AdminUser();
        $user->setEmail('admin@example.com');
        $this->assertEquals('admin@example.com', $user->getUserIdentifier());
    }
} 