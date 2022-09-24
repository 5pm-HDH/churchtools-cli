<?php


namespace CTExport\Commands\Traits;


use CTApi\Requests\EventRequest;
use CTApi\Requests\PersonRequest;
use CTApi\Requests\SongRequest;

trait LoadEvents
{
    protected function loadEvents(string $startDate, string $endDate): array
    {
        return EventRequest::where("from", $startDate)->where("to", $endDate)->get();
    }

    protected function loadMyEvents(): array
    {
        return PersonRequest::whoami()->requestEvents()?->get() ?? [];
    }

    protected function loadEventsForCalendar(string $startDate, string $endDate, array $calendarIds): array
    {
        $events = $this->loadEvents($startDate, $endDate);
        return array_values(array_filter($events, function ($event) use ($calendarIds) {
            $calId = $event?->getCalendar()?->getDomainIdentifier();
            return is_null($calId) ? false : in_array($calId, $calendarIds);
        }));
    }

    protected function loadSongs(): array
    {
        return SongRequest::all();
    }

    protected function loadSongsOfCategories(array $songCategoryIds): array
    {
        return SongRequest::where("song_category_ids", $songCategoryIds)->get();
    }

    protected function loadSongsShouldPractice(bool $shouldPractice): array
    {
        return SongRequest::where("practice", $shouldPractice)->get();
    }

    protected function loadSongsWithTitle(string $title): array
    {
        return SongRequest::where("name", $title)->get();
    }

}