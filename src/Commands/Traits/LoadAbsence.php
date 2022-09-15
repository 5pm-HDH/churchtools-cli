<?php


namespace CTExport\Commands\Traits;


use CTApi\Requests\AbsenceRequest;

trait LoadAbsence
{
    protected function loadAbsence(int $personId, string $startDate, string $endDate): array
    {
        return AbsenceRequest::forPerson($personId)
            ->where("from_date", $startDate)
            ->where("to_date", $endDate)
            ->get();
    }
}