<?php

namespace Tests\Unit\Policies;

use App\Models\GameSession;
use App\Models\User;
use App\Policies\GameSessionPolicy;
use PHPUnit\Framework\TestCase;

class GameSessionPolicyTest extends TestCase
{
    public function test_owner_can_update_started_session_but_not_completed_session(): void
    {
        $owner = (new User)->forceFill(['id' => 10]);
        $session = (new GameSession)->forceFill(['user_id' => 10, 'status' => 'started']);
        $policy = new GameSessionPolicy;

        $this->assertTrue($policy->update($owner, $session));
        $session->status = 'completed';
        $this->assertFalse($policy->update($owner, $session));
    }

    public function test_other_user_cannot_update_session(): void
    {
        $user = (new User)->forceFill(['id' => 11]);
        $session = (new GameSession)->forceFill(['user_id' => 10, 'status' => 'started']);

        $this->assertFalse((new GameSessionPolicy)->update($user, $session));
    }
}
