<?php


namespace CTExport\Commands\Traits;


use CTApi\Models\Calendar;
use CTApi\Requests\Traits\Pagination;

trait LoadCalendars
{
    use Pagination;

    protected function loadCalendars(): array
    {
        // TODO: Replace this Code with ChurchTools-API-Request: https://github.com/5pm-HDH/churchtools-api/issues/90
        $data = $this->collectDataFromPages('/api/calendars');
        return Calendar::createModelsFromArray($data);
    }
}