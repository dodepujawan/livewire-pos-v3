<?php

use App\Models\LauncherGroup;
use Livewire\Component;

new class extends Component
{
    public ?int $editingId = null;
    public string $key = '';
    public string $label = '';
    public ?string $icon = null;
    public int $sortOrder = 0;
    public bool $isActive = true;

    protected function rules(): array
    {
        $rules = [
            'label' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'sortOrder' => ['required', 'integer', 'min:0'],
            'isActive' => ['boolean'],
        ];

        if (! $this->editingId) {
            $rules['key'] = ['required', 'string', 'max:50', 'unique:launcher_groups,key'];
        }

        return $rules;
    }

    public function render()
    {
        return $this->view([
            'groups' => LauncherGroup::withCount('menus')->orderBy('sort_order')->get(),
        ])
        ->layout('layouts::app')
        ->title('Launcher Group Manager');
    }

    public function save(): void
    {
        $validated = $this->validate();

        $data = [
            'label'       => $validated['label'],
            'icon'        => $validated['icon'],
            'sort_order'  => $validated['sortOrder'],
            'is_active'   => $validated['isActive'],
        ];

        if ($this->editingId) {
            LauncherGroup::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Launcher group updated.');
        } else {
            $data['key'] = $validated['key'];
            LauncherGroup::create($data);
            session()->flash('success', 'Launcher group created.');
        }

        $this->resetForm();
    }

    public function edit(LauncherGroup $group): void
    {
        $this->editingId = $group->id;
        $this->key = $group->key;
        $this->label = $group->label;
        $this->icon = $group->icon;
        $this->sortOrder = $group->sort_order;
        $this->isActive = $group->is_active;
    }

    public function delete(LauncherGroup $group): void
    {
        if ($group->menus()->exists()) {
            session()->flash(
                'error',
                'Group tidak dapat dihapus karena masih digunakan oleh menu.'
            );
            return;
        }

        $group->delete();
        session()->flash('success', 'Launcher group deleted.');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->key = '';
        $this->label = '';
        $this->icon = null;
        $this->sortOrder = 0;
        $this->isActive = true;
    }
};
