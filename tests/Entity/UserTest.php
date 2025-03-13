<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserEmail()
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $this->assertEquals('test@example.com', $user->getEmail());
    }

    public function testUserRoles()
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $this->assertContains('ROLE_ADMIN', $user->getRoles());
    }
}
