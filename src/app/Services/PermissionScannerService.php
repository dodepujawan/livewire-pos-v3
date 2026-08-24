<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;
use ReflectionClass;

class PermissionScannerService
{
    public function scan(): array
    {
        $permissions = [];

        foreach (Route::getRoutes() as $route) {

            // Scan Livewire Page
            if ($component = $route->getAction('livewire_component')) {
                $permissions = array_merge(
                    $permissions,
                    $this->scanLivewire($component)
                );
            }

            // Scan Controller
            if ($controller = $route->getAction('controller')) {
                $permissions = array_merge(
                    $permissions,
                    $this->scanController($controller)
                );
            }
        }

        return array_values(array_unique($permissions));
    }

    protected function scanController(string $controller): array
    {
        if (! class_exists($controller)) {
            return [];
        }

        $reflection = new ReflectionClass($controller);

        if (! $reflection->hasProperty('additionalPermissions')) {
            return [];
        }

        $instance = app($controller);

        return $reflection
            ->getProperty('additionalPermissions')
            ->getValue($instance) ?? [];
    }

    protected function scanLivewire(string $component): array
    {
        $component = str_replace('pages::', '', $component);

        $segments = explode('.', $component);

        $name = array_pop($segments);

        $path = resource_path(
            'views/pages/' .
            implode('/', $segments) .
            '/⚡' . $name .
            '/' . $name . '.php'
        );

        if (! file_exists($path)) {
            return [];
        }

        return $this->extractPermissions($path);
    }

    protected function extractPermissions(string $path): array
    {
        $content = file_get_contents($path);

        if ($content === false) {
            return [];
        }

        if (! preg_match(
            '/protected\s+array\s+\$additionalPermissions\s*=\s*\[(.*?)\];/is',
            $content,
            $matches
        )) {
            return [];
        }

        preg_match_all(
            '/["\']([^"\']+)["\']/',
            $matches[1],
            $permissions
        );

        return array_unique($permissions[1]);
    }
}
