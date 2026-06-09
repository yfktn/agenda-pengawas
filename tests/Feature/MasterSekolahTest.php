<?php

namespace Tests\Feature;

use App\Models\MasterSekolah;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterSekolahTest extends TestCase
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
            ->get('/admin/master-sekolahs')
            ->assertStatus(200);
    }

    public function test_admin_can_view_create_page(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/master-sekolahs/create')
            ->assertStatus(200);
    }

    public function test_admin_can_view_edit_page(): void
    {
        $sekolah = MasterSekolah::factory()->create();

        $this->actingAs($this->admin)
            ->get("/admin/master-sekolahs/{$sekolah->id}/edit")
            ->assertStatus(200);
    }

    public function test_list_page_displays_master_sekolahs(): void
    {
        MasterSekolah::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/master-sekolahs');

        $response->assertStatus(200);
        $this->assertCount(3, MasterSekolah::all());
    }

    public function test_create_master_sekolah_via_model(): void
    {
        $data = MasterSekolah::factory()->make()->toArray();

        MasterSekolah::create($data);

        $this->assertDatabaseHas('master_sekolahs', [
            'nisn' => $data['nisn'],
            'nama_sekolah' => $data['nama_sekolah'],
        ]);
    }

    public function test_edit_master_sekolah_via_model(): void
    {
        $sekolah = MasterSekolah::factory()->create();

        $sekolah->update(['nama_sekolah' => 'SDN Baru 01']);

        $this->assertDatabaseHas('master_sekolahs', [
            'id' => $sekolah->id,
            'nama_sekolah' => 'SDN Baru 01',
        ]);
    }

    public function test_delete_master_sekolah_via_model(): void
    {
        $sekolah = MasterSekolah::factory()->create();

        $sekolah->delete();

        $this->assertModelMissing($sekolah);
    }

    public function test_nisn_must_be_unique(): void
    {
        MasterSekolah::factory()->create(['nisn' => '1234567890']);

        $this->expectException(QueryException::class);

        MasterSekolah::factory()->create(['nisn' => '1234567890']);
    }

    public function test_nisn_is_required(): void
    {
        $this->expectException(QueryException::class);

        MasterSekolah::create([
            'nama_sekolah' => 'SDN Contoh',
            'alamat' => 'Jl. Contoh',
        ]);
    }

    public function test_nama_sekolah_is_required(): void
    {
        $this->expectException(QueryException::class);

        MasterSekolah::create([
            'nisn' => '0987654321',
            'alamat' => 'Jl. Contoh',
        ]);
    }
}
