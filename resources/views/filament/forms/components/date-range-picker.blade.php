<div
    x-data="{
        state: $wire.entangle('data.start_date'),
        endState: $wire.entangle('data.end_date'),
        selectedDates: [],
        init() {
            this.calendar = new FullCalendar.Calendar(this.$refs.calendar, {
                initialView: 'dayGridMonth',
                selectable: true,
                select: (info) => {
                    this.state = info.startStr;
                    this.endState = info.endStr;
                    this.selectedDates = [info.startStr, info.endStr];
                    this.calendar.unselect();
                    this.updateSelection();
                },
                unselect: () => {
                    this.selectedDates = [];
                    this.updateSelection();
                },
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: ''
                },
                selectConstraint: {
                    start: new Date().toISOString().split('T')[0],
                },
                selectOverlap: false,
            });
            this.calendar.render();
            
            if (this.state && this.endState) {
                this.selectedDates = [this.state, this.endState];
                this.updateSelection();
            }
        },
        updateSelection() {
            const events = this.calendar.getEvents();
            events.forEach(event => event.remove());
            
            if (this.selectedDates.length === 2) {
                this.calendar.addEvent({
                    start: this.selectedDates[0],
                    end: this.selectedDates[1],
                    display: 'background',
                    backgroundColor: '#3b82f6'
                });
            }
        }
    }"
    class="relative"
    wire:ignore
>
    <div class="mb-4">
        <label class="inline-block text-sm font-medium text-gray-700 dark:text-gray-300">
            Select Leave Dates
        </label>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Click and drag on the calendar to select your leave dates
        </p>
    </div>

    <div x-ref="calendar" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 p-4"></div>

    @push('scripts')
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
        <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css' rel='stylesheet' />
    @endpush
</div> 