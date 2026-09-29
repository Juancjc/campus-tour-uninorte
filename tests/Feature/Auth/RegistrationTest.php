<?php

namespace Tests\Feature\Auth;

use App\Jobs\SendWelcomeEmailJob;
use App\Models\Profession;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_registration_screen_renders_with_professions(): void
    {
        Profession::factory()->create(['name' => 'Desenvolvedor de Software']);

        $response = $this->get(route('register'));

        $response->assertOk()->assertSee('Desenvolvedor de Software');
    }

    public function test_valid_registration_creates_user_authenticates_and_dispatches_welcome_job(): void
    {
        Queue::fake([SendWelcomeEmailJob::class]);
        $profession = Profession::factory()->create();

        $response = $this->post(route('register'), [
            'name' => 'Ana Estudante',
            'email' => 'ana@example.com',
            'school_name' => 'Escola Estadual do Acre',
            'profession_id' => $profession->id,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirectToRoute('dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'ana@example.com',
            'school_name' => 'Escola Estadual do Acre',
            'profession_id' => $profession->id,
        ]);
        Queue::assertPushed(SendWelcomeEmailJob::class, fn (SendWelcomeEmailJob $job): bool => $job->userId === auth()->id());
    }

    public function test_registration_rejects_missing_school_and_profession(): void
    {
        Queue::fake([SendWelcomeEmailJob::class]);

        $response = $this->from(route('register'))->post(route('register'), [
            'name' => 'Ana Estudante',
            'email' => 'ana@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('register'))->assertInvalid(['school_name', 'profession_id']);
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'ana@example.com']);
        Queue::assertNothingPushed();
    }
}
