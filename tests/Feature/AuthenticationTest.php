<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guests_are_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    #[Test]
    public function admin_can_sign_in_and_see_the_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@ledger.test',
            'password' => 'password',
        ]);

        $this->post('/login', [
            'email' => 'admin@ledger.test',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->actingAs($user)->get('/')->assertOk()->assertSee('Financial summary');
    }
}
