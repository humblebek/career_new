@props([
    'id' => '',
    'name' => '',
    'type' => 'text',
    'value' => '',
    'required' => false,
    'autofocus' => false,
    'autocomplete' => '',
    'placeholder' => ''
])

<input
    {{ $attributes->merge([
        'class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm',
        'id' => $id,
        'name' => $name,
        'type' => $type,
        'value' => $value,
        'required' => $required,
        'autofocus' => $autofocus,
        'autocomplete' => $autocomplete,
        'placeholder' => $placeholder
    ]) }}
>
