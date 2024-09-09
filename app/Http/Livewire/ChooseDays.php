<?php

namespace App\Http\Livewire;

use Livewire\Component;

class ChooseDays extends Component
{
    public $activeTab = 'Mon';
    public $days = [
        'Mon' => [],
        'Tue' => [],
        'Wed' => [],
        'Thu' => [],
        'Fri' => [],
        'Sat' => [],
        'Sun' => [],
    ];

    public function mount($servicedays=null)
    {
        if($servicedays) {
            $this->days = array_merge($this->days, $servicedays);
        }
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function removeTimeFromDay($day, $index)
    {
        unset($this->days[$day][$index]);
    }

    public function addTimeToDay($day)
    {
        $this->days[$day][] = [
            'start' => '00:00',
            'end' => '00:00',
        ];
    }

    public function render()
    {
        return view('livewire.choose-days');
    }
}
