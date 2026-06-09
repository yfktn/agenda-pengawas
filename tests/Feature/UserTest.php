<?php

namespace Tests\Feature;

use App\Models\MasterSekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'Admin',
        ]);
    }

    public function test_admin_can_view_list_page(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/users')
            ->assertStatus(200);
    }

    public function test_admin_can_view_create_page(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/users/create')
            ->assertStatus(200);
    }

    public function test_admin_can_view_edit_page(): void
    {
        $user = User::factory()->create(['role' => 'Pengawas']);

        $this->actingAs($this->admin)
            ->get("/admin/users/{$user->id}/edit")
            ->assertStatus(200);
    }

    public function test_list_page_displays_users(): void
    {
        User::factory()->count(3)->create(['role' => 'Pengawas']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/users');

        $response->assertStatus(200);
        $this->assertCount(4, User::all());
    }

    public function test_create_user_via_model(): void
    {
        $data = User::factory()->make()->toArray();
        $data['role'] = 'OperatorSekolah';
        $data['password'] = 'secret123';

        User::create($data);

        $this->assertDatabaseHas('users', [
            'email' => $data['email'],
            'role' => 'OperatorSekolah',
        ]);
    }

    public function test_edit_user_via_model(): void
    {
        $user = User::factory()->create(['role' => 'Pengawas']);

        $user->update(['name' => 'Updated Name']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_delete_user_via_model(): void
    {
        $user = User::factory()->create(['role' => 'Pengawas']);

        $user->delete();

        $this->assertModelMissing($user);
    }

    public function test_password_is_hashed(): void
    {
        $user = User::factory()->create([
            'password' => 'plaintext',
            'role' => 'Admin',
        ]);

        $this->assertNotEquals('plaintext', $user->password);
        $this->assertTrue(Hash::check('plaintext', $user->password));
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create([
            'email' => 'same@example.com',
            'role' => 'Admin',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::factory()->create([
            'email' => 'same@example.com',
            'role' => 'Pengawas',
        ]);
    }

    public function test_operator_hanya_di_satu_sekolah(): void
    {
        $sekolah = MasterSekolah::factory()->create();

        User::factory()->create([
            'role' => 'OperatorSekolah',
            'sekolah_id' => $sekolah->id,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::factory()->create([
            'role' => 'OperatorSekolah',
            'sekolah_id' => $sekolah->id,
        ]);
    }

    public function test_pengawas_bisa_di_beberapa_sekolah(): void
    {
        $sekolahs = MasterSekolah::factory()->count(2)->create();
        $pengawas = User::factory()->create(['role' => 'Pengawas']);

        $pengawas->penugasanSekolah()->attach($sekolahs->pluck('id'));

        $this->assertCount(2, $pengawas->penugasanSekolah);
    }

    public function test_satu_sekolah_hanya_satu_pengawas(): void
    {
        $sekolah = MasterSekolah::factory()->create();
        $pengawasA = User::factory()->create(['role' => 'Pengawas']);

        $pengawasA->penugasanSekolah()->attach($sekolah->id);

        $pengawasB = User::factory()->create(['role' => 'Pengawas']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::table('penugasan_pengawas')->insert([
            'user_id' => $pengawasB->id,
            'master_sekolah_id' => $sekolah->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_satu_sekolah_hanya_satu_operator(): void
    {
        $sekolah = MasterSekolah::factory()->create();

        User::factory()->create([
            'role' => 'OperatorSekolah',
            'sekolah_id' => $sekolah->id,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::factory()->create([
            'role' => 'OperatorSekolah',
            'sekolah_id' => $sekolah->id,
        ]);
    }
}
