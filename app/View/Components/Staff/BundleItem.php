<?php

namespace App\View\Components\Staff;

use Illuminate\View\Component;

class BundleItem extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public $item, public $entityType, public $bundleId)
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.staff.bundle-item');
    }
}
