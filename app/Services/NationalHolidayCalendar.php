<?php

namespace App\Services;

class NationalHolidayCalendar
{
    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return config('rapi.national_holidays', []);
    }

    public function isHoliday(string $date): bool
    {
        return array_key_exists($date, $this->all());
    }

    /**
     * @return array<int, array{
     *     id: string,
     *     title: string,
     *     start: string,
     *     allDay: bool,
     *     display: string,
     *     backgroundColor: string,
     *     borderColor: string,
     *     textColor: string,
     *     extendedProps: array{type: string}
     * }>
     */
    public function events(): array
    {
        $events = [];

        foreach ($this->all() as $date => $name) {
            $events[] = [
                'id' => 'national-holiday-'.$date,
                'title' => $name,
                'start' => $date,
                'allDay' => true,
                'display' => 'background',
                'backgroundColor' => '#fee2e2',
                'borderColor' => '#fecaca',
                'textColor' => '#b91c1c',
                'extendedProps' => ['type' => 'national_holiday'],
            ];
        }

        return $events;
    }
}
