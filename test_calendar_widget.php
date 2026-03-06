<?php

use App\Filament\Resources\Drills\Widgets\DrillCalendarWidget;
use Carbon\Carbon;

$widget = new DrillCalendarWidget();
$widget->mount();

echo "Month: " . $widget->currentMonth . "\n";
echo "Year: " . $widget->currentYear . "\n";

$data = $widget->getCalendarDataProperty();
echo "Calendar Data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";

$events = $widget->getEventsProperty();
echo "Events Count: " . $events->flatten(1)->count() . "\n";

if ($events->flatten(1)->count() > 0) {
    echo "First Event: " . json_encode($events->flatten(1)->first(), JSON_PRETTY_PRINT) . "\n";
} else {
    echo "No events found for this month.\n";
}
