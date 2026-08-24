<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Spatie\Permission\PermissionRegistrar;

class PermissionMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_user_with_permission_can_access_route(): void
    {
        $permission = Permission::findOrCreate(
            'master.barang.view',
            'web'
        );

        $role = Role::findOrCreate(
            'Test Role',
            'web'
        );

        $role->givePermissionTo($permission);

        $user = User::factory()->create();
        $user->assignRole($role);

        Route::middleware(['auth', 'permission:master.barang.view'])
            ->get('/test-permission-allowed', function () {
                return response('OK');
            })
            ->name('master.barang.list');

        $response = $this->actingAs($user)
            ->get('/test-permission-allowed');

        $response->assertOk();
        $response->assertSee('OK');
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $role = Role::findOrCreate(
            'Test Role Without Permission',
            'web'
        );

        $user = User::factory()->create();
        $user->assignRole($role);

        Permission::findOrCreate(
            'master.barang.view',
            'web'
        );

        Route::middleware(['auth', 'permission:master.barang.view'])
            ->get('/test-permission-denied', function () {
                return response('OK');
            })
            ->name('master.barang.list');

        $response = $this->actingAs($user)
            ->get('/test-permission-denied');

        $response->assertForbidden();
    }
}
