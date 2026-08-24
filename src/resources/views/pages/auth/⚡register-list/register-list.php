<?php

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public function delete($id)
    {
        User::findOrFail($id)->delete();

        session()->flash('message', 'User berhasil dihapus');
    }

    public function render()
    {
        return $this->view([
            'regUsers' => User::latest()->paginate(5),
        ])
        ->layout('layouts::app')
        ->title('User List');
    }
};
