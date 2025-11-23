@props(['type' => 'info'])

@php
    $colors = [
        'success' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        'danger' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        'info' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        'gray' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    ];

    $classes = $colors[$type] ?? $colors['info'];
@endphp

<span {{ $attributes->merge(['class' => "text-xs font-medium mr-2 px-2.5 py-0.5 rounded $classes"]) }}>
    {{ $slot }}
</span>
