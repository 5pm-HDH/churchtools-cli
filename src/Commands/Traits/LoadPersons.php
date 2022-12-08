<?php


namespace CTExport\Commands\Traits;


use CTApi\CTLog;
use CTApi\Requests\PersonRequest;

trait LoadPersons
{
    protected function loadBirthdayPerson(string $birthdayBefore, array $statusIds): array
    {
        CTLog::enableConsoleLog();
        CTLog::enableFileLog();
        return PersonRequest::where('status_ids', $statusIds)
            ->where('birthday_before', $birthdayBefore)
            ->get();
    }
}