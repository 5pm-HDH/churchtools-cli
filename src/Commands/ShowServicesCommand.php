<?php


namespace CTExport\Commands;


use CTExport\Commands\Traits\LoadServices;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(
    name: 'show:services',
    description: 'Show all services with service-groups.',
    hidden: false,
)]
class ShowServicesCommand extends AbstractCommand
{
    use LoadServices;

    const SERVICE_GROUP_IDS = "service_group_ids";

    protected function configure()
    {
        $this->addOption(self::SERVICE_GROUP_IDS, null, InputArgument::OPTIONAL, "List of service-group-ids separated by comma.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $serviceGroupIds = $this->getOptionAsIntegerList($input, self::SERVICE_GROUP_IDS);

        $progressBar = new ProgressBar($output);
        $servicesAsTable = $this->loadServicesTable($serviceGroupIds, $progressBar);
        $progressBar->finish();
        $output->writeln("");

        $table = new Table($output);
        $table->setHeaders(["Id", "Name", "Service-Group Id", "Service-Group Name"])
            ->setRows($servicesAsTable);
        $table->render();

        return Command::SUCCESS;
    }
}