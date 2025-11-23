@props(['active'])

@php
$classes = ($active ?? false)
            ? 'flex items-center px-6 py-3 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 border-r-4 border-blue-500 transition-colors duration-200'
            : 'flex items-center px-6 py-3 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100 transition-colors duration-200';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
