<?php

namespace App\Http\Livewire;

use Livewire\Component;

class SwitchBundle extends Component
{
    public $switchBundleToggle;

    public function mount()
    {
        $this->emit('switchBundleUpdated', $this->switchBundleToggle);
    }

    public function switchBundle()
    {
        $this->switchBundleToggle = !$this->switchBundleToggle;
        $this->emit('switchBundleUpdated', $this->switchBundleToggle);
    }

    public function render()
    {
        return view('livewire.switch-bundle');
    }
}
