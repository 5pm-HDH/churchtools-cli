<?php


namespace CTExport\Commands\Traits;


use CTApi\Requests\ResourceBookingsRequest;
use CTApi\Requests\ResourceRequest;

trait LoadResources
{
    protected function loadResources(): array
    {
        return ResourceRequest::all();
    }

    protected function loadBookings($resourceIds, $startDate, $endDate): array
    {
        return ResourceBookingsRequest::forResources($resourceIds)->where("from", $startDate)->where("to", $endDate)->get();
    }
}