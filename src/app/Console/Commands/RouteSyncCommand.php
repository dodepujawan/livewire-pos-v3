<?php

namespace App\Console\Commands;

use App\Models\SystemRoute;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class RouteSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'framework:route-sync {--safe : Add missing routes without updating or deleting existing route records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize Laravel named routes into system_routes table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $routes = Route::getRoutes();
        $syncedRoutes = [];
        $stats = [
            'created' => 0,
            'updated' => 0,
            'preserved' => 0,
        ];
        $safe = (bool) $this->option('safe');

        foreach ($routes as $route) {
            $routeName = $route->getName();
            if (!$routeName) {
                continue;
            }
            if (!$this->shouldSyncRoute($routeName)) {
                continue;
            }
            $syncedRoutes[] = $routeName;
            $status = $this->syncRoute($routeName, $safe);
            $stats[$status]++;
        }

        if (! $safe) {
            $this->deleteMissingRoutes($syncedRoutes);
        }

        $this->newLine();
        $this->info('Route Synchronization Completed');
        $this->line("Created : {$stats['created']}");
        $this->line("Updated : {$stats['updated']}");
        if ($safe) {
            $this->line("Preserved existing : {$stats['preserved']}");
            $this->line('Missing route records were not deleted.');
        }

        return self::SUCCESS;
    }

    private function syncRoute(string $routeName, bool $safe): string
    {
        $route = SystemRoute::firstOrNew([
            'route_name' => $routeName,
        ]);
        $isNew = !$route->exists;

        if (! $isNew && $safe) {
            return 'preserved';
        }

        $displayName = $this->generateDisplayName($routeName);
        if ($route->display_name !== $displayName) {
            $route->display_name = $displayName;
        }
        $route->save();
        return $isNew ? 'created' : 'updated';
    }

    private function deleteMissingRoutes(array $syncedRoutes): void
    {
        if (empty($syncedRoutes)) {
            return;
        }

        SystemRoute::query()
            ->whereNotIn('route_name', $syncedRoutes)
            ->doesntHave('menus')
            ->delete();
    }

    /**
    * Determine whether the route should be synchronized.
    */
    private function shouldSyncRoute(string $routeName): bool
    {
        $ignoredPrefixes = [
            'livewire.',
            'ignition.',
            'debugbar.',
            'sanctum.',
        ];

        foreach ($ignoredPrefixes as $prefix) {
            if (Str::startsWith($routeName, $prefix)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generate display name from route name.
     */
    private function generateDisplayName(string $routeName): string
    {
        return Str::of($routeName)
            ->replace('.', ' ')
            ->replace('-', ' ')
            ->title()
            ->toString();
    }
}
