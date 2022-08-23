<?php


namespace CTExport\Commands\Traits;


use CTApi\Requests\EventRequest;

trait LoadEvents
{
    protected function loadEvents(string $startDate, string $endDate): array
    {
        return EventRequest::where("from", $startDate)->where("to", $endDate)->get();
    }

    protected function loadEventsForCalendar(string $startDate, string $endDate, array $calendarIds): array
    {
        $events = $this->loadEvents($startDate, $endDate);
        return array_values(array_filter($events, function ($event) use ($calendarIds) {
            $calId = $event?->getCalendar()?->getDomainIdentifier();
            return is_null($calId) ? false : in_array($calId, $calendarIds);
        }));
    }
}