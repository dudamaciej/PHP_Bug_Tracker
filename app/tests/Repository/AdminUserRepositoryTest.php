<?php

namespace App\Tests\Repository;

use App\Entity\AdminUser;
use App\Repository\AdminUserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class AdminUserRepositoryTest extends KernelTestCase
{
    private AdminUserRepository $repository;
    private $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->repository = static::getContainer()->get(AdminUserRepository::class);
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
    }

    public function testSaveWithFlush(): void
    {
        $adminUser = new AdminUser();
        $adminUser->setEmail('test@example.com');
        $adminUser->setPassword('hashedpassword');

        $this->repository->save($adminUser, true);

        $this->assertNotNull($adminUser->getId());

        // Clean up
        $this->entityManager->remove($adminUser);
        $this->entityManager->flush();
    }

    public function testSaveWithoutFlush(): void
    {
        $adminUser = new AdminUser();
        $adminUser->setEmail('test2@example.com');
        $adminUser->setPassword('hashedpassword');

        $this->repository->save($adminUser, false);

        // Should not have an ID yet since flush wasn't called
        $this->assertNull($adminUser->getId());

        // Clean up
        $this->entityManager->remove($adminUser);
        $this->entityManager->flush();
    }

    public function testRemoveWithFlush(): void
    {
        // First create a user
        $adminUser = new AdminUser();
        $adminUser->setEmail('test3@example.com');
        $adminUser->setPassword('hashedpassword');
        $this->entityManager->persist($adminUser);
        $this->entityManager->flush();

        $userId = $adminUser->getId();

        // Now remove it
        $this->repository->remove($adminUser, true);

        // Verify it's gone
        $removedUser = $this->repository->find($userId);
        $this->assertNull($removedUser);
    }

    public function testRemoveWithoutFlush(): void
    {
        // First create a user
        $adminUser = new AdminUser();
        $adminUser->setEmail('test4@example.com');
        $adminUser->setPassword('hashedpassword');
        $this->entityManager->persist($adminUser);
        $this->entityManager->flush();

        $userId = $adminUser->getId();

        // Remove without flush
        $this->repository->remove($adminUser, false);

        // Should still exist since flush wasn't called
        $existingUser = $this->repository->find($userId);
        $this->assertNotNull($existingUser);

        // Clean up
        $this->entityManager->remove($existingUser);
        $this->entityManager->flush();
    }

    public function testUpgradePassword(): void
    {
        // Create a user
        $adminUser = new AdminUser();
        $adminUser->setEmail('test5@example.com');
        $adminUser->setPassword('oldpassword');
        $this->entityManager->persist($adminUser);
        $this->entityManager->flush();

        $newPassword = 'newhashedpassword';
        $this->repository->upgradePassword($adminUser, $newPassword);

        $this->assertEquals($newPassword, $adminUser->getPassword());

        // Clean up
        $this->entityManager->remove($adminUser);
        $this->entityManager->flush();
    }

    public function testUpgradePasswordWithUnsupportedUser(): void
    {
        // Test with a different user type that doesn't implement the required interface
        $this->expectException(UnsupportedUserException::class);

        // Create a mock that implements the interface but is not an AdminUser
        $unsupportedUser = $this->createMock(PasswordAuthenticatedUserInterface::class);

        // This will throw the exception because we're not passing a proper AdminUser
        $this->repository->upgradePassword($unsupportedUser, 'newpassword');
    }

    public function testFindByEmail(): void
    {
        // Create a user
        $adminUser = new AdminUser();
        $adminUser->setEmail('test6@example.com');
        $adminUser->setPassword('hashedpassword');
        $this->entityManager->persist($adminUser);
        $this->entityManager->flush();

        $foundUser = $this->repository->findOneBy(['email' => 'test6@example.com']);

        $this->assertNotNull($foundUser);
        $this->assertEquals('test6@example.com', $foundUser->getEmail());

        // Clean up
        $this->entityManager->remove($foundUser);
        $this->entityManager->flush();
    }

    public function testFindAll(): void
    {
        $users = $this->repository->findAll();

        $this->assertIsArray($users);
        $this->assertGreaterThanOrEqual(0, count($users));
    }

    public function testFindBy(): void
    {
        // Create a user with specific role
        $adminUser = new AdminUser();
        $adminUser->setEmail('test7@example.com');
        $adminUser->setPassword('hashedpassword');
        $adminUser->setRoles(['ROLE_ADMIN']);
        $this->entityManager->persist($adminUser);
        $this->entityManager->flush();

        $foundUsers = $this->repository->findBy(['email' => 'test7@example.com']);

        $this->assertIsArray($foundUsers);
        $this->assertCount(1, $foundUsers);
        $this->assertEquals('test7@example.com', $foundUsers[0]->getEmail());

        // Clean up
        $this->entityManager->remove($foundUsers[0]);
        $this->entityManager->flush();
    }
}
