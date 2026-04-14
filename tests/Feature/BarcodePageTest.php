<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarcodePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_barcode_page(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();

        $response = $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->get(route('admin.loans.index'));

        $response->assertOk();
        $response->assertSee('Barcode Barang');
        $response->assertSee('Setting Barcode');
        $response->assertSee('Ukuran kertas');
        $response->assertSee('Varian grid A4');
        $response->assertSee('Format download gambar');
        $response->assertSee('Container Preview');
        $response->assertSee('3 x 4 Grid (12 kartu)');
        $response->assertSee('12 kartu per halaman');
        $response->assertSee('Download PDF');
        $response->assertSee('Print Epson L4150');
        $response->assertSee('Label 107');
        $response->assertDontSee('Kategori');
        $response->assertDontSee('Kondisi');
        $response->assertDontSee('Pilih Aset');
    }

    public function test_label107_hides_a4_grid_dropdown(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();

        $response = $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->get(route('admin.loans.index', ['format' => 'label107']));

        $response->assertOk();
        $response->assertSee('Label 107');
        $response->assertDontSee('Varian grid A4');
        $response->assertDontSee('Kategori');
        $response->assertDontSee('Kondisi');
    }
}
