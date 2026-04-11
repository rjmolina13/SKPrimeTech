<?php

namespace Tests\Feature;

use App\Filament\Widgets\ActiveFormsWidget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ActiveFormsWidgetRefreshTest extends TestCase
{
    use RefreshDatabase;

    public function test_refresh_button_uses_a_dedicated_loading_spinner(): void
    {
        Role::firstOrCreate(['name' => 'admin']);

        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user);

        Livewire::test(ActiveFormsWidget::class)
            ->assertSeeHtml('wire:click="$refresh"')
            ->assertSeeHtml('wire:loading.remove.delay.shortest')
            ->assertSeeHtml('wire:loading.delay.shortest')
            ->assertSeeHtml('wire:target="$refresh"')
            ->assertSeeHtml('animate-spin text-primary-500');
    }
}
