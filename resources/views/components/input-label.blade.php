@props(['for' => '', 'value' => ''])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-gray-700']) }} for="{{ $for }}">
    {{ $value ?: $slot }}
</label>
