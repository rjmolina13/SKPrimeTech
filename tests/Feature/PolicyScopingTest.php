<?php

namespace Tests\Feature;

use App\Models\Barangay;
use App\Models\Municipality;
use App\Models\User;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

use Illuminate\Foundation\Testing\RefreshDatabase;

class PolicyScopingTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        // Ensure role exists for testing
        Role::firstOrCreate(['name' => 'municipal']);
    }

    public function test_municipal_user_cannot_view_other_municipality_item(): void
    {
        $m1 = Municipality::create(['name' => 'M1', 'code' => 'M1']);
        $m2 = Municipality::create(['name' => 'M2', 'code' => 'M2']);
        
        $b1 = Barangay::create(['name' => 'B1', 'code' => 'B1', 'municipality_id' => $m1->id]);
        $b2 = Barangay::create(['name' => 'B2', 'code' => 'B2', 'municipality_id' => $m2->id]);

        $user = User::create(['name' => 'U', 'email' => 'u@example.com', 'password' => 'password', 'municipality_id' => $m1->id]);
        $user->assignRole('municipal');

        $this->be($user);
        
        $this->assertTrue($user->can('view', $b1));
        $this->assertFalse($user->can('view', $b2));
    }
}
