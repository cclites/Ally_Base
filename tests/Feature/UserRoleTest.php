<?php

namespace Tests\Feature;

use App\Caregiver;
use App\Client;
use App\OfficeUser;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function testRolesCanCreateUser()
    {
        $user = factory(Caregiver::class)->create();
        $this->assertInstanceOf(Caregiver::class, $user);
        $this->assertInstanceOf(User::class, $user->user);

        $user = factory(Client::class)->create();
        $this->assertInstanceOf(Client::class, $user);
        $this->assertInstanceOf(User::class, $user->user);


        $user = factory(OfficeUser::class)->create();
        $this->assertInstanceOf(OfficeUser::class, $user);
        $this->assertInstanceOf(User::class, $user->user);
    }

    public function testUserCanReachRole()
    {
        $user = factory(Caregiver::class)->create();
        $user = $user->user;
        $this->assertInstanceOf(Caregiver::class, $user->role);
    }
}
