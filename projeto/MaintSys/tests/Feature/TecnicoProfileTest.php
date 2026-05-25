<?php

namespace Tests\Feature;

use App\Models\Tecnico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TecnicoProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_tecnico_also_creates_linked_user_profile(): void
    {
        $admin = User::factory()->create(['role' => 'admin_master']);

        $response = $this->actingAs($admin)->post(route('tecnicos.store'), [
            'nome' => 'Carlos Tecnico',
            'matricula' => 'TEC-1001',
            'email' => 'carlos.tecnico@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'especialidade' => 'Eletrica',
            'telefone' => '(19) 99999-9999',
            'ativo' => '1',
        ]);

        $response->assertRedirect(route('tecnicos.index'));

        $tecnico = Tecnico::where('email', 'carlos.tecnico@example.com')->firstOrFail();
        $user = User::where('email', 'carlos.tecnico@example.com')->firstOrFail();

        $this->assertSame($user->id, $tecnico->user_id);
        $this->assertSame('Carlos Tecnico', $user->name);
        $this->assertSame('usuario', $user->role);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    public function test_updating_tecnico_keeps_linked_user_profile_in_sync(): void
    {
        $admin = User::factory()->create(['role' => 'admin_master']);
        $tecnico = Tecnico::factory()->create();

        $response = $this->actingAs($admin)->put(route('tecnicos.update', $tecnico), [
            'nome' => 'Carlos Atualizado',
            'matricula' => $tecnico->matricula,
            'email' => 'carlos.atualizado@example.com',
            'password' => 'newsecret123',
            'password_confirmation' => 'newsecret123',
            'especialidade' => 'Mecanica',
            'telefone' => '(19) 98888-7777',
            'ativo' => '1',
        ]);

        $response->assertRedirect(route('tecnicos.index'));

        $tecnico->refresh();
        $user = $tecnico->user()->firstOrFail();

        $this->assertSame('Carlos Atualizado', $tecnico->nome);
        $this->assertSame('carlos.atualizado@example.com', $tecnico->email);
        $this->assertSame('Carlos Atualizado', $user->name);
        $this->assertSame('carlos.atualizado@example.com', $user->email);
        $this->assertTrue(Hash::check('newsecret123', $user->password));
    }

    public function test_deleting_tecnico_also_removes_auto_created_user_profile(): void
    {
        $admin = User::factory()->create(['role' => 'admin_master']);
        $tecnico = Tecnico::factory()->create();
        $userId = $tecnico->user_id;

        $response = $this->actingAs($admin)->delete(route('tecnicos.destroy', $tecnico));

        $response->assertRedirect(route('tecnicos.index'));
        $this->assertDatabaseMissing('tecnicos', ['id' => $tecnico->id]);
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }
}
