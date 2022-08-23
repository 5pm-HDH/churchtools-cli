<?php


namespace CTExport\Commands\ShowCommands;


use CTExport\Commands\Traits\LoadServices;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;


#[AsCommand(
    name: 'show:services',
    description: 'Show all services with service-groups.',
    hidden: false,
)]
class ShowServicesCommand extends ShowTableCommand
{
    use LoadServices;

    const SERVICE_GROUP_IDS = "service-group-ids";

    protected function configure()
    {
        parent::configure();
        $this->addOption(self::SERVICE_GROUP_IDS, null, InputArgument::OPTIONAL, "List of service-group-ids separated by comma.");
    }

    protected function getTableBuilder(InputInterface $input): TableBuilder
    {
        $filterServiceGroupIds = $this->getOptionAsIntegerList($input, self::SERVICE_GROUP_IDS);

        return TableBuilder::forServices($this->loadServices(), $filterServiceGroupIds);
    }
}