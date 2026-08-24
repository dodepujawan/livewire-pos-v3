<?php

namespace App\Console\Commands;

use App\Models\SystemRoute;
use App\Services\PermissionScannerService;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use App\Services\PermissionNameService;

class PermissionSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'framework:permission-sync';

    /**
     * The console command description.
     */
    protected $description = 'Synchronize system routes into Spatie permissions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $permissionName = app(PermissionNameService::class);

        $routePermissions = SystemRoute::query()
            ->pluck('route_name')
            ->map(fn ($route) => $permissionName->fromRoute($route));

        $additionalPermissions = app(PermissionScannerService::class)->scan();

        $routePermissions = $routePermissions
            ->merge($additionalPermissions)
            ->unique()
            ->values();

        $created = 0;

        foreach ($routePermissions as $permissionName) {

            $permission = Permission::findOrCreate(
                $permissionName,
                'web'
            );

            if ($permission->wasRecentlyCreated) {
                $created++;
            }
        }

        // Hapus permission route yang sudah tidak ada
        Permission::query()
            ->where('guard_name', 'web')
            ->whereDoesntHave('roles')
            ->whereNotIn('name', $routePermissions)
            ->delete();

        $this->newLine();

        $this->info('Permission Synchronization Completed');

        $this->line('Permission : ' . $routePermissions->count());
        $this->line('Created    : ' . $created);

        return self::SUCCESS;
    }
}
