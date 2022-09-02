<?php


namespace CTExport\Commands\Traits;


use CTApi\Requests\PersonRequest;

trait LoadPersons
{
    protected function loadBirthdays(string $startDate, string $endDate): array
    {
        return PersonRequest::birthdays()
            ->where("start_date", $startDate)
            ->where("end_date", $endDate)
            ->get();
    }

    protected function loadBirthdaysForGroups(string $startDate, string $endDate, array $groupIds): array
    {
        return PersonRequest::birthdays()
            ->where("start_date", $startDate)
            ->where("end_date", $endDate)
            ->where("group_ids", $groupIds)
            ->get();
    }

    protected function loadBirthdaysForMyGroups(string $startDate, string $endDate): array
    {
        return PersonRequest::birthdays()
            ->where("start_date", $startDate)
            ->where("end_date", $endDate)
            ->where("my_groups", true)
            ->get();
    }
}