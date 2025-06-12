<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <span @class([
                        'flex w-3 h-3 rounded-full',
                        'animate-pulse' => $status === 'Pending',
                        'bg-warning-500' => $status === 'Pending',
                        'bg-success-500' => $status === 'Approved',
                        'bg-danger-500' => $status === 'Rejected',
                    ])></span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Status
                    </p>
                    <h3 @class([
                        'text-lg font-semibold',
                        'text-warning-600 dark:text-warning-400' => $status === 'Pending',
                        'text-success-600 dark:text-success-400' => $status === 'Approved',
                        'text-danger-600 dark:text-danger-400' => $status === 'Rejected',
                    ])>
                        {{ $status }}
                    </h3>
                </div>
            </div>
        </div>
    </div>
</div> 