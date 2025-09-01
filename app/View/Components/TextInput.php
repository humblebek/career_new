<?php

namespace App\View\Components;

use Illuminate\View\Component;

class TextInput extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $id = '',
        public string $name = '',
        public string $type = 'text',
        public string $value = '',
        public bool $required = false,
        public bool $autofocus = false,
        public string $autocomplete = '',
        public string $placeholder = '',
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.text-input');
    }
}
