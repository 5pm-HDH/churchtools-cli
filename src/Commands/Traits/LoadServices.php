<?php


namespace CTExport\Commands\Traits;


use CTApi\Requests\ServiceRequest;

trait LoadServices
{
    /**
     * Return array of Services.
     * @return array
     */
    protected function loadServices(): array
    {
        return ServiceRequest::all();
    }
}