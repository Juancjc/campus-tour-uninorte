<?php

namespace Tests\Feature\Auth;

use App\Jobs\SendWelcomeEmailJob;
use App\Models\Profession;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function test_registration_normalizes_and_stores_the_full_name(): void
    {
        Queue::fake([SendWelcomeEmailJob::class]);
        $profession = Profession::factory()->create();

        $response = $this->post(route('register'), $this->validRegistrationData($profession, [
            'name' => '  Ana   Clara D\'Ávila  ',
            'email' => 'ana.clara@example.com',
        ]));

        $response->assertRedirectToRoute('dashboard');
        $this->assertDatabaseHas('users', [
            'name' => 'Ana Clara D\'Ávila',
            'email' => 'ana.clara@example.com',
        ]);
    }

    public function test_registration_requires_a_full_name(): void
    {
        Queue::fake([SendWelcomeEmailJob::class]);
        $profession = Profession::factory()->create();

        $response = $this->from(route('register'))->post(
            route('register'),
            $this->validRegistrationData($profession, ['name' => 'Ana']),
        );

        $response->assertRedirect(route('register'))
            ->assertInvalid(['name' => 'Informe seu nome completo, com nome e sobrenome.']);
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'ana@example.com']);
        Queue::assertNothingPushed();
    }

    #[DataProvider('inappropriateNames')]
    public function test_registration_rejects_inappropriate_names_and_common_obfuscations(string $name): void
    {
        Queue::fake([SendWelcomeEmailJob::class]);
        $profession = Profession::factory()->create();

        $response = $this->from(route('register'))->post(
            route('register'),
            $this->validRegistrationData($profession, ['name' => $name]),
        );

        $response->assertRedirect(route('register'))
            ->assertInvalid(['name' => 'Informe um nome adequado para aparecer no ranking.']);
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'ana@example.com']);
        Queue::assertNothingPushed();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function inappropriateNames(): array
    {
        return [
            'number substitutions' => ['João P0rr4'],
            'mixed number substitutions' => ['M3rd4 Silva'],
            'multiple vowel substitutions' => ['João C4r4lh0'],
            'one and zero substitutions' => ['João V14d0'],
            'at-sign substitution' => ['João @romb4d0'],
            'dollar-sign abbreviation' => ['João V$F'],
            'hyphenated letters' => ['P-U-T-A Silva'],
            'repeated letters' => ['Fiiiilho da Puuuta'],
            'punctuated abbreviation' => ['João F.D.P'],
            'accented term' => ['João Cuzão'],
        ];
    }

    #[DataProvider('legitimateFullNames')]
    public function test_registration_accepts_legitimate_full_names(string $name): void
    {
        Queue::fake([SendWelcomeEmailJob::class]);
        $profession = Profession::factory()->create();
        $email = sprintf('student-%s@example.com', md5($name));

        $response = $this->post(
            route('register'),
            $this->validRegistrationData($profession, ['name' => $name, 'email' => $email]),
        );

        $response->assertRedirectToRoute('dashboard');
        $this->assertDatabaseHas('users', ['name' => $name, 'email' => $email]);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function legitimateFullNames(): array
    {
        return [
            'accents and apostrophe' => ['Ana Clara D\'Ávila'],
            'surname containing an innocent partial match' => ['Ana Picanço da Silva'],
            'surname with a common syllable' => ['Paulo da Cunha'],
            'surname starting with an ambiguous sequence' => ['Maria Poranga Silva'],
            'ambiguous but legitimate surname' => ['Ana Carolina Pinto'],
        ];
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

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validRegistrationData(Profession $profession, array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ana Estudante',
            'email' => 'ana@example.com',
            'school_name' => 'Escola Estadual do Acre',
            'profession_id' => $profession->id,
            'password' => 'password',
            'password_confirmation' => 'password',
        ], $overrides);
    }
}
