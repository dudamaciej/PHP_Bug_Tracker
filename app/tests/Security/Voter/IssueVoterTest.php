<?php

namespace App\Tests\Security\Voter;

use App\Entity\AdminUser;
use App\Entity\Issue;
use App\Security\Voter\IssueVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class IssueVoterTest extends TestCase
{
    private IssueVoter $voter;
    private TokenInterface $token;
    private AdminUser $adminUser;
    private Issue $issue;

    protected function setUp(): void
    {
        $this->voter = new IssueVoter();
        $this->adminUser = new AdminUser();
        $this->issue = new Issue();
        $this->issue->setTitle('Test Issue');
        
        $this->token = $this->createMock(TokenInterface::class);
    }

    public function testVoteOnAttributeWithAdminUser(): void
    {
        $this->adminUser->setRoles(['ROLE_ADMIN']);
        $this->token->method('getUser')->willReturn($this->adminUser);
        
        $this->assertEquals(1, $this->voter->vote($this->token, $this->issue, [IssueVoter::VIEW]));
        $this->assertEquals(1, $this->voter->vote($this->token, $this->issue, [IssueVoter::CREATE]));
        $this->assertEquals(1, $this->voter->vote($this->token, $this->issue, [IssueVoter::EDIT]));
        $this->assertEquals(1, $this->voter->vote($this->token, $this->issue, [IssueVoter::DELETE]));
    }

    public function testVoteOnAttributeWithInvalidAttribute(): void
    {
        $this->adminUser->setRoles(['ROLE_ADMIN']);
        $this->token->method('getUser')->willReturn($this->adminUser);
        
        $this->assertEquals(0, $this->voter->vote($this->token, $this->issue, ['INVALID_ATTRIBUTE']));
    }
} 