<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0 p-3 bg-primary-100 dark:bg-primary-900/50 rounded-lg">
                    <x-dynamic-component 
                        :component="$icon" 
                        class="w-6 h-6 text-primary-600 dark:text-primary-400"
                    />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Leave Period
                    </p>
                    <div class="mt-1 flex items-center space-x-2">
                        <span class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ $startDate }}
                        </span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                        <span class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ $endDate }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 