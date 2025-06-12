<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0 p-3 bg-{{ $color }}-100 dark:bg-{{ $color }}-900/50 rounded-lg">
                    <x-dynamic-component 
                        :component="$icon" 
                        class="w-6 h-6 text-{{ $color }}-600 dark:text-{{ $color }}-400"
                    />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ $label }}
                    </p>
                    <h3 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ (int)$value }}
                    </h3>
                </div>
            </div>
        </div>
    </div>
</div> 