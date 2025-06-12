<x-filament-widgets::widget>
    <x-filament::section>
        <div
            x-data="{
                leaves: @js($leaves),
                init() {
                    const calendar = new FullCalendar.Calendar(this.$refs.calendar, {
                        initialView: 'dayGridMonth',
                        events: this.leaves,
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek'
                        },
                    });
                    calendar.render();
                }
            }"
        >
            <div x-ref="calendar"></div>
        </div>
    </x-filament::section>

    @push('scripts')
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    @endpush
</x-filament-widgets::widget> 