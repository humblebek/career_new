<?php

namespace App\View\Components;

use Illuminate\View\Component;

class InputLabel extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $for = '',
        public string $value = '',
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.input-label');
    }
}
