<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;
use Illuminate\Support\Carbon;

class DateRangePicker extends Field
{
    protected string $view = 'forms.components.date-range-picker';

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function (DateRangePicker $component, $state) {
            if (is_string($state)) {
                $dates = explode(' to ', $state);
                if (count($dates) === 2) {
                    $component->state([
                        'start' => $dates[0],
                        'end' => $dates[1],
                    ]);
                }
            }
        });

        $this->dehydrateStateUsing(function ($state) {
            if (is_array($state) && isset($state['start']) && isset($state['end'])) {
                return $state['start'] . ' to ' . $state['end'];
            }
            return null;
        });

        $this->reactive();
    }
} 