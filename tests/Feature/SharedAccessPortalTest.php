<?php

namespace Tests\Feature;

use App\Models\SharedAccessLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SharedAccessPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_access_route_renders_for_valid_token_and_tracks_usage(): void
    {
        $link = SharedAccessLink::create([
            'name' => 'Municipal Public Access',
            'scope' => [
                'visible_sections' => ['dashboard', 'records'],
                'record_statuses' => ['draft', 'submitted', 'under_review', 'approved', 'rejected'],
            ],
            'is_active' => true,
        ]);

        $response = $this->get(route('access.view', ['token' => $link->token]));

        $response->assertOk();
        $response->assertSee('Shared Access Portal');

        $link->refresh();

        $this->assertSame(1, $link->access_count);
        $this->assertNotNull($link->last_accessed_at);
    }

    public function test_legacy_shared_route_still_works(): void
    {
        $link = SharedAccessLink::create([
            'name' => 'Legacy Shared Route',
            'scope' => [
                'visible_sections' => ['dashboard', 'records'],
                'record_statuses' => ['draft', 'submitted', 'under_review', 'approved', 'rejected'],
            ],
            'is_active' => true,
        ]);

        $response = $this->get(route('shared.view', ['token' => $link->token]));

        $response->assertOk();
    }

    public function test_expired_or_inactive_link_is_forbidden(): void
    {
        $expiredLink = SharedAccessLink::create([
            'name' => 'Expired Link',
            'scope' => ['visible_sections' => ['dashboard', 'records']],
            'expires_at' => Carbon::now()->subMinute(),
            'is_active' => true,
        ]);

        $inactiveLink = SharedAccessLink::create([
            'name' => 'Inactive Link',
            'scope' => ['visible_sections' => ['dashboard', 'records']],
            'is_active' => false,
        ]);

        $this->get(route('access.view', ['token' => $expiredLink->token]))->assertForbidden();
        $this->get(route('access.view', ['token' => $inactiveLink->token]))->assertForbidden();
    }
}
