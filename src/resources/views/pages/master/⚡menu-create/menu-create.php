<?php

use App\Models\Menu;
use App\Models\SystemRoute;
use Livewire\Component;

new class extends Component
{
    public ?int $parent_id = null;
    public ?int $system_route_id = null;
    public string $title = '';
    public ?string $icon = null;
    public int $sort_order = 1;
    public bool $is_sidebar = true;
    public ?string $launcher_group = null;
    public bool $titleCustomized = false;

    protected function rules(): array
    {
        return [
            'parent_id' => [
                'nullable',
                'exists:menus,id',
            ],
            'system_route_id' => [
                'nullable',
                'exists:system_routes,id',
            ],
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'icon' => [
                'nullable',
                'string',
                'max:255',
            ],
            'sort_order' => [
                'required',
                'integer',
                'min:1',
            ],
            'is_sidebar' => [
                'boolean',
            ],
            'launcher_group' => [
                'nullable',
                'string',
                'max:50',
            ],

        ];
    }

    public function updatedTitle(): void
    {
        $this->titleCustomized = true;
    }

    public function updatedSystemRouteId($value): void
    {
        if (blank($value)) {
            return;
        }

        $route = SystemRoute::find($value);

        if (!$route) {
            return;
        }

        if (!$this->titleCustomized) {
            $this->title = $route->display_name;
        }
    }

    public function save(): void{
        $validated = $this->validate();

        Menu::create($validated);

        session()->flash(
            'success',
            'Menu berhasil ditambahkan.'
        );
        $this->redirectRoute('master.menu.list', navigate: true);
        // $this->redirectRoute('master.menu.list');
    }


    public function render()
    {
        return $this->view([
            'parentMenus' => Menu::query()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(),
            'systemRoutes' => SystemRoute::orderBy('route_name')->get(),
            'launcherGroups' => \App\Models\LauncherGroup::where('is_active', true)->orderBy('sort_order')->get(),
        ])
        ->layout('layouts::app')
        ->title('Create Menu');
    }
};
