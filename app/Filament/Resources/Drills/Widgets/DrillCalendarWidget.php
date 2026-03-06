<?php

namespace App\Filament\Resources\Drills\Widgets;

use Filament\Widgets\Widget;
use Carbon\Carbon;
use App\Models\ActivityExecution;
use Illuminate\Support\Collection;

class DrillCalendarWidget extends Widget
{
    protected string $view = 'filament.resources.drills.widgets.drill-calendar-widget';
    
    protected int | string | array $columnSpan = 2;
    
    public $currentMonth;
    public $currentYear;
    
    public function mount()
    {
        $now = Carbon::now();
        $this->currentMonth = $now->month;
        $this->currentYear = $now->year;
    }
    
    public function nextMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }
    
    public function previousMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }
    
    public function getEventsProperty()
    {
        $start = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        
        return ActivityExecution::query()
            ->whereHas('activity', function ($query) {
                $query->where('tipo', 'simulacro');
            })
            ->whereBetween('fecha_programada', [$start, $end])
            ->with(['activity.drill'])
            ->get()
            ->map(function ($execution) {
                return [
                    'id' => $execution->id,
                    'title' => $execution->activity->drill->nombre ?? $execution->activity->nombre ?? 'Simulacro',
                    'date' => $execution->fecha_programada, // Carbon object
                    'day' => $execution->fecha_programada->day,
                    'status' => $execution->estado,
                ];
            })
            ->groupBy('day');
    }
    
    public function getCalendarDataProperty()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1);
        $daysInMonth = $date->daysInMonth;
        
        // 0=Sun, 1=Mon, ..., 6=Sat
        $dayOfWeek = $date->dayOfWeek;
        
        // Transform to 0=Mon, ..., 6=Sun
        // If Sun(0) -> 6
        // If Mon(1) -> 0
        $firstDayOffset = ($dayOfWeek + 6) % 7;
        
        return [
            'monthName' => ucfirst($date->locale('es')->monthName),
            'year' => $date->year,
            'daysInMonth' => $daysInMonth,
            'firstDayOffset' => $firstDayOffset,
        ];
    }
}
