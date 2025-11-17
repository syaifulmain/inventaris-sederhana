<?php

namespace App\Livewire\User;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
class Dashboard extends Component
{
    public $title = 'Dashboard';
    public $breadcrumb = 'Dashboard';

    public function render()
    {
        return view('livewire.dashboard');
    }
}
