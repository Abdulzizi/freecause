<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\User;
use App\Models\UserLevel;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminPermissionMatrixTest extends TestCase
{
    use RefreshDatabase;

    private const UNGUARDED = [
        'admin.login',
        'admin.login.post',
        'admin.logout',
        'admin.dashboard',
    ];

    public function test_every_admin_route_permission_pair_is_in_the_matrix(): void
    {
        $matrix = Permission::matrix();
        $checked = 0;

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();

            if (! is_string($name) || ! str_starts_with($name, 'admin.') || in_array($name, self::UNGUARDED, true)) {
                continue;
            }

            $guards = array_values(array_filter(
                $route->gatherMiddleware(),
                fn ($middleware) => is_string($middleware) && str_starts_with($middleware, 'permission:')
            ));

            $this->assertNotEmpty($guards, "Route {$name} has no permission middleware.");

            foreach ($guards as $guard) {
                [$module, $action] = explode(',', substr($guard, strlen('permission:')), 2);

                $this->assertContains(
                    $action,
                    $matrix[$module] ?? [],
                    "Route {$name} uses {$guard} but Permission::matrix() has no {$module},{$action} pair."
                );
            }

            $checked++;
        }

        $this->assertGreaterThan(0, $checked);
    }

    public function test_permissions_store_saves_petitions_view_and_delete(): void
    {
        $this->seed(UserLevelSeeder::class);

        $admin = User::factory()->create([
            'level_id' => UserLevel::where('name', 'admin')->value('id'),
            'verified' => true,
        ]);
        $target = UserLevel::where('name', 'user')->value('id');

        $this->actingAs($admin, 'admin')
            ->from(route('admin.system.permissions'))
            ->post(route('admin.system.permissions.store'), [
                'level_id' => $target,
                'module' => 'petitions',
                'actions' => ['view', 'delete'],
            ])
            ->assertRedirect(route('admin.system.permissions'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('permissions', ['level_id' => $target, 'module' => 'petitions', 'action' => 'view']);
        $this->assertDatabaseHas('permissions', ['level_id' => $target, 'module' => 'petitions', 'action' => 'delete']);
    }
}
