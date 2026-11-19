<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetCardHeightTest extends TestCase
{
    use RefreshDatabase;

    public function test_asset_summary_cards_are_equal_in_height_and_have_vertical_scroll(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();

        $response = $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->get(route('admin.assets.index'));

        $response->assertOk();

        $content = $response->getContent();

        // 1. Verify all 3 cards exist
        $this->assertStringContainsString('Total Barang', $content);
        $this->assertStringContainsString('Jumlah Berdasarkan Kategori', $content);
        $this->assertStringContainsString('Laptop per Merk', $content);

        // 2. Verify all cards have h-100 to stay equal in height (sejajar)
        $this->assertStringContainsString('card asset-stat-card h-100', $content);
        $this->assertStringContainsString('card asset-list-card assets-list-card h-100', $content);

        // 3. Verify vertical scroll wrapper exists
        $this->assertStringContainsString('asset-list-scroll', $content);

        // 4. Verify CSS contains scrollbar and fixed equal height rules
        $this->assertStringContainsString('.asset-stat-card,', $content);
        $this->assertStringContainsString('.assets-list-card', $content);
        $this->assertStringContainsString('overflow-y: auto;', $content);
    }
}
