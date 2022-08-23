<?php


namespace CTExport\Commands\Traits;


use CTApi\Requests\ServiceRequest;
use Symfony\Component\Console\Helper\ProgressBar;

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

    /**
     * Returns Table of Services. With Columns:
     * <li>Service-Id</li>
     * <li>Service-Name</li>
     * <li>Service-Group-Id</li>
     * <li>Service-Group-Name</li>
     * The table is ordered by Service-Group-Name.
     * @param array $filterServiceGroups
     */
    protected function loadServicesTable(array $filterServiceGroups, ?ProgressBar $progressBar)
    {
        $allServices = ServiceRequest::all();

        if (!empty($filterServiceGroups)) {
            $allServices = array_filter($allServices, function ($service) use ($filterServiceGroups) {
                return in_array($service->getServiceGroupId(), $filterServiceGroups);
            });
        }

        if ($progressBar != null) {
            $progressBar->setMaxSteps(sizeof($allServices));
            $progressBar->start();
        }

        $serviceTable = array_map(function ($service) use ($progressBar) {
            if ($progressBar != null) {
                $progressBar->advance();
            }
            return [
                $service->getId(),
                $service->getName(),
                $service->requestServiceGroup()?->getId(),
                $service->requestServiceGroup()?->getName()
            ];
        }, $allServices);

        usort($serviceTable, function ($valueA, $valueB) {
            return strcmp($valueA[3], $valueB[3]); // index 3 = ServiceGroupName
        });

        return $serviceTable;
    }

}