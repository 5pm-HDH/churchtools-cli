<?php


namespace CTExport\Commands\ShowCommands;


use CTExport\Commands\Traits\LoadResources;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;


#[AsCommand(
    name: 'show:bookings',
    description: 'Show bookings for given resource.',
    hidden: false,
)]
class ShowBookingsCommand extends ShowTableCommand
{
    use LoadResources;

    const ARGUMENT_RESOURCE_IDS = "ResourceIds";

    protected function configure()
    {
        parent::configure();
        $this->addOptionStartDate();
        $this->addOptionEndDate();
        $this->addArgument(self::ARGUMENT_RESOURCE_IDS, InputArgument::REQUIRED, "List of resource Ids.");
    }

    protected function getTableBuilder(InputInterface $input): TableBuilder
    {
        $startDate = $this->getOptionStartDate($input);
        $endDate = $this->getOptionEndDate($input);
        $resourceIds = $this->getArgumentAsIntegerList($input, self::ARGUMENT_RESOURCE_IDS);

        return TableBuilder::forBookings($this->loadBookings($resourceIds, $startDate, $endDate));
    }
}