<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('profile.update'), [
            'nombre' => 'Nombre Actualizado',
            'apellido' => 'Apellido Actualizado',
        ])->assertRedirect(route('profile.edit'));

        $this->assertSame('Nombre Actualizado', $user->refresh()->nombre);
        $this->assertSame('Apellido Actualizado', $user->apellido);
    }
}
