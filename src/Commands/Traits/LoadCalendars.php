<?php


namespace CTExport\Commands\Traits;


use CTApi\Models\Calendar;
use CTApi\Requests\CalendarRequest;
use CTApi\Requests\Traits\Pagination;

trait LoadCalendars
{
    use Pagination;

    protected function loadCalendars(): array
    {
        return CalendarRequest::all();
    }
}