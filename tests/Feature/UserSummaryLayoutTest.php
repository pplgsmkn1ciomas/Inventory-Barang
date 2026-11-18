<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSummaryLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_summary_card_renders_all_components_inside_hero_and_overview(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'admin')->firstOrFail();

        $response = $this->withSession([
            'admin_access' => [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'granted_at' => now()->getTimestamp(),
            ],
        ])->get(route('admin.users.index'));

        $response->assertOk();

        $content = $response->getContent();

        // 1. Verify user-summary-hero exists
        $this->assertStringContainsString('user-summary-hero', $content);

        // 2. Verify user-summary-overview exists
        $this->assertStringContainsString('user-summary-overview', $content);

        // 3. Verify user-summary-stats is inside user-summary-overview
        $overviewStart = strpos($content, 'user-summary-overview');
        $overviewEnd = strpos($content, 'user-summary-ring-panel');
        $this->assertNotFalse($overviewStart);
        $this->assertNotFalse($overviewEnd);
        $overviewBlock = substr($content, $overviewStart, $overviewEnd - $overviewStart);

        $this->assertStringContainsString('user-summary-stats', $overviewBlock, 'user-summary-stats must be inside user-summary-overview');

        // 4. Verify all titles and labels are preserved
        $response->assertSee('Ringkasan data pengguna');
        $response->assertSee('Resume / Review');
        $response->assertSee('Kelengkapan data wajah');
        $response->assertSee('Total Pengguna');
        $response->assertSee('Kategori Role');
        $response->assertSee('Kelas');
        $response->assertSee('Data Wajah Siap');

        // 5. Verify no extra user-summary-stats outside hero
        $heroEnd = strpos($content, 'accordion user-create-accordion');
        $afterHero = substr($content, $overviewEnd, $heroEnd - $overviewEnd);
        $this->assertStringNotContainsString('user-summary-stats px-4 pb-4', $afterHero);
    }
}
