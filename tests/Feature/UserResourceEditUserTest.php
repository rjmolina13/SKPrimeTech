<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserResourceEditUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_editing_user_without_new_password_keeps_existing_password_hash(): void
    {
        Role::firstOrCreate(['name' => 'super_admin']);

        $currentMunicipality = Municipality::create(['name' => 'Municipality A', 'code' => 'MUN-A']);
        $newMunicipality = Municipality::create(['name' => 'Municipality B', 'code' => 'MUN-B']);

        $editor = User::factory()->create();
        $editor->assignRole('super_admin');

        $targetUser = User::factory()->create([
            'municipality_id' => $currentMunicipality->id,
        ]);
        $targetUser->assignRole('super_admin');

        $originalPasswordHash = $targetUser->password;

        $this->actingAs($editor);

        Livewire::test(EditUser::class, ['record' => $targetUser->getKey()])
            ->fillForm([
                'name' => $targetUser->name,
                'email' => $targetUser->email,
                'password' => '',
                'roles' => ['super_admin'],
                'municipality_id' => $newMunicipality->id,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $targetUser->refresh();

        $this->assertSame($originalPasswordHash, $targetUser->password);
        $this->assertSame($newMunicipality->id, $targetUser->municipality_id);
        $this->assertTrue($targetUser->hasRole('super_admin'));
    }
}
