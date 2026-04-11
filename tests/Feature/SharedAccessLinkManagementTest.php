<?php

namespace Tests\Feature;

use App\Filament\Resources\SharedAccessLinks\SharedAccessLinkResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SharedAccessLinkManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_url_helpers_generate_truncated_url_and_copy_action_js_payload(): void
    {
        $token = str_repeat('a', 40);
        $fullUrl = SharedAccessLinkResource::makePublicUrl($token);
        $truncatedUrl = SharedAccessLinkResource::makeTruncatedPublicUrl($token);
        $copyActionJs = SharedAccessLinkResource::makeCopyLinkActionJs($token);

        $this->assertSame(route('access.view', ['token' => $token]), $fullUrl);
        $this->assertSame(Str::limit($fullUrl, 52), $truncatedUrl);
        $this->assertStringContainsString($token, $copyActionJs);
        $this->assertStringContainsString('const url =', $copyActionJs);
        $this->assertStringContainsString('Public URL copied', $copyActionJs);
        $this->assertStringContainsString('Failed to copy public URL', $copyActionJs);
    }
}
