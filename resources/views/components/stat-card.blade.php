@props(['title', 'value', 'icon' => null, 'color' => 'blue'])

@php
    $colors = [
        'blue' => 'text-blue-500 bg-blue-100 dark:bg-blue-900 dark:text-blue-200',
        'green' => 'text-green-500 bg-green-100 dark:bg-green-900 dark:text-green-200',
        'red' => 'text-red-500 bg-red-100 dark:bg-red-900 dark:text-red-200',
        'yellow' => 'text-yellow-500 bg-yellow-100 dark:bg-yellow-900 dark:text-yellow-200',
        'purple' => 'text-purple-500 bg-purple-100 dark:bg-purple-900 dark:text-purple-200',
    ];
    $iconClass = $colors[$color] ?? $colors['blue'];
@endphp

<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 flex items-center">
    @if($icon)
        <div class="p-3 rounded-full mr-4 {{ $iconClass }}">
            {{ $icon }}
        </div>
    @endif
    <div>
        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
            {{ $title }}
        </div>
        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            {{ $value }}
        </div>
        @if(isset($description))
            <div class="text-xs text-gray-400 mt-1">
                {{ $description }}
            </div>
        @endif
    </div>
</div>
