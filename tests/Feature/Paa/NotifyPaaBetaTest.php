<?php

namespace Tests\Feature\Paa;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotifyPaaBetaTest extends TestCase
{
    use RefreshDatabase;

    public function test_envia_notificacion_beta_a_todos_los_usuarios(): void
    {
        User::factory()->count(3)->create();

        $this->artisan('paa:notify-beta')->assertSuccessful();

        // Filament sendToDatabase crea un registro por usuario en la tabla notifications.
        $this->assertDatabaseCount('notifications', 3);
    }

    public function test_no_falla_si_no_hay_usuarios(): void
    {
        $this->artisan('paa:notify-beta')->assertSuccessful();

        $this->assertDatabaseCount('notifications', 0);
    }
}
