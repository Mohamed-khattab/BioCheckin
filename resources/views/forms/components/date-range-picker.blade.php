<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{
            state: $wire.entangle('{{ $getStatePath() }}'),
            init() {
                this.picker = flatpickr(this.$refs.calendar, {
                    mode: 'range',
                    dateFormat: 'Y-m-d',
                    minDate: 'today',
                    showMonths: 1,
                    defaultDate: 'today',
                    inline: true,
                    animate: true,
                    onChange: (selectedDates) => {
                        if (selectedDates.length === 2) {
                            this.state = {
                                start: selectedDates[0].toISOString().split('T')[0],
                                end: selectedDates[1].toISOString().split('T')[0]
                            };
                        }
                    },
                    onReady: () => {
                        if (this.state?.start && this.state?.end) {
                            this.picker.setDate([this.state.start, this.state.end]);
                        }
                    }
                });
            }
        }"
        wire:ignore
        class="w-full"
    >
        <div x-ref="calendar" class="w-full"></div>
    </div>
</x-dynamic-component>

@push('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        .flatpickr-calendar.inline {
            width: 100% !important;
            box-shadow: none;
            margin: 0;
            background-color: transparent;
            border: none;
        }
        
        .flatpickr-calendar {
            font-family: inherit;
        }
        
        .flatpickr-months {
            background: #f8fafc;
            border-radius: 0.75rem;
            padding: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .flatpickr-current-month {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
        }
        
        .flatpickr-weekdays {
            margin: 0.5rem 0;
            background: transparent;
        }
        
        .flatpickr-weekday {
            font-size: 0.875rem;
            font-weight: 600;
            color: #64748b;
        }
        
        .flatpickr-days {
            width: 100% !important;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.75rem;
            background: white;
        }
        
        .dayContainer {
            width: 100% !important;
            min-width: 100%;
            max-width: 100%;
            justify-content: space-between;
            padding: 0 0.5rem;
        }
        
        .flatpickr-day {
            width: 40px;
            height: 40px;
            line-height: 40px;
            margin: 2px;
            border-radius: 0.5rem;
            font-size: 0.9375rem;
            font-weight: 500;
            color: #334155;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }
        
        .flatpickr-day:hover {
            background: #f1f5f9;
            border-color: #e2e8f0;
        }
        
        .flatpickr-day.selected,
        .flatpickr-day.startRange,
        .flatpickr-day.endRange {
            background: #2563eb;
            border-color: #2563eb;
            color: white;
            font-weight: 600;
        }
        
        .flatpickr-day.inRange {
            background: #dbeafe;
            border-color: #bfdbfe;
            color: #1e40af;
            box-shadow: -5px 0 0 #dbeafe, 5px 0 0 #dbeafe;
        }
        
        .flatpickr-day.today {
            border-color: #2563eb;
            color: #2563eb;
            font-weight: 600;
        }
        
        .flatpickr-day.today:hover {
            background: #eff6ff;
        }
        
        .flatpickr-day.disabled {
            color: #94a3b8;
            text-decoration: line-through;
            opacity: 0.5;
        }
        
        .flatpickr-months .flatpickr-prev-month,
        .flatpickr-months .flatpickr-next-month {
            padding: 5px;
            fill: #64748b;
        }
        
        .flatpickr-months .flatpickr-prev-month:hover,
        .flatpickr-months .flatpickr-next-month:hover {
            fill: #334155;
        }
        
        /* Dark mode styles */
        .dark .flatpickr-calendar.inline {
            background-color: transparent;
        }
        
        .dark .flatpickr-months {
            background: #0f172a;
        }
        
        .dark .flatpickr-current-month {
            color: #f1f5f9;
        }
        
        .dark .flatpickr-weekday {
            color: #94a3b8;
        }
        
        .dark .flatpickr-days {
            background: #1e293b;
            border-color: #334155;
        }
        
        .dark .flatpickr-day {
            color: #e2e8f0;
        }
        
        .dark .flatpickr-day:hover {
            background: #334155;
            border-color: #475569;
        }
        
        .dark .flatpickr-day.inRange {
            background: #1e40af;
            border-color: #1e40af;
            color: #e2e8f0;
            box-shadow: -5px 0 0 #1e40af, 5px 0 0 #1e40af;
        }
        
        .dark .flatpickr-day.selected,
        .dark .flatpickr-day.startRange,
        .dark .flatpickr-day.endRange {
            background: #3b82f6;
            border-color: #3b82f6;
        }
        
        .dark .flatpickr-day.today {
            border-color: #3b82f6;
            color: #3b82f6;
        }
        
        .dark .flatpickr-day.today:hover {
            background: #1e40af;
            color: white;
        }
        
        .dark .flatpickr-day.disabled {
            color: #64748b;
        }
        
        .dark .flatpickr-months .flatpickr-prev-month,
        .dark .flatpickr-months .flatpickr-next-month {
            fill: #94a3b8;
        }
        
        .dark .flatpickr-months .flatpickr-prev-month:hover,
        .dark .flatpickr-months .flatpickr-next-month:hover {
            fill: #e2e8f0;
        }
    </style>
@endpush 