<?php

namespace Tests\Feature;

use App\Filament\Resources\FormSubmissions\Pages\ListFormSubmissions;
use App\Models\User;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FormSubmissionsTableFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_submitted_by_filter_exists_for_admin(): void
    {
        Role::firstOrCreate(['name' => 'admin']);

        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user);

        Livewire::test(ListFormSubmissions::class)
            ->assertTableFilterExists('submitted_by_name', function (SelectFilter $filter): bool {
                $options = array_values($filter->getOptions());

                return count($options) === count(array_unique($options));
            })
            ->assertTableFilterVisible('submitted_by_name');
    }

    public function test_submitted_by_filter_is_hidden_for_municipal_user(): void
    {
        Role::firstOrCreate(['name' => 'municipal']);

        $user = User::factory()->create();
        $user->assignRole('municipal');
        $this->actingAs($user);

        Livewire::test(ListFormSubmissions::class)
            ->assertTableFilterHidden('submitted_by_name');
    }
}
