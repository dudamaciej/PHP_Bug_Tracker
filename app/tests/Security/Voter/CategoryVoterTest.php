<?php

namespace App\Tests\Security\Voter;

use App\Entity\AdminUser;
use App\Entity\Category;
use App\Security\Voter\CategoryVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CategoryVoterTest extends TestCase
{
    private CategoryVoter $voter;
    private TokenInterface $token;
    private AdminUser $adminUser;
    private Category $category;

    protected function setUp(): void
    {
        $this->voter = new CategoryVoter();
        $this->adminUser = new AdminUser();
        $this->category = new Category();
        $this->category->setName('Test Category');
        
        $this->token = $this->createMock(TokenInterface::class);
    }

    public function testVoteOnAttributeWithAdminUser(): void
    {
        $this->adminUser->setRoles(['ROLE_ADMIN']);
        $this->token->method('getUser')->willReturn($this->adminUser);
        
        $this->assertEquals(1, $this->voter->vote($this->token, $this->category, [CategoryVoter::VIEW]));
        $this->assertEquals(1, $this->voter->vote($this->token, $this->category, [CategoryVoter::CREATE]));
        $this->assertEquals(1, $this->voter->vote($this->token, $this->category, [CategoryVoter::EDIT]));
        $this->assertEquals(1, $this->voter->vote($this->token, $this->category, [CategoryVoter::DELETE]));
    }

    public function testVoteOnAttributeWithInvalidAttribute(): void
    {
        $this->adminUser->setRoles(['ROLE_ADMIN']);
        $this->token->method('getUser')->willReturn($this->adminUser);
        
        $this->assertEquals(0, $this->voter->vote($this->token, $this->category, ['INVALID_ATTRIBUTE']));
    }

    public function testVoteOnAttributeWithMultipleAttributes(): void
    {
        $this->adminUser->setRoles(['ROLE_ADMIN']);
        $this->token->method('getUser')->willReturn($this->adminUser);
        
        $result = $this->voter->vote($this->token, $this->category, [CategoryVoter::VIEW, CategoryVoter::EDIT]);
        $this->assertEquals(1, $result);
    }
} 