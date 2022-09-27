<?php


namespace CTExport\Commands\ReportCommands;


use CTExport\Commands\ReportCommands\ReportBuilders\GroupHierarchyReport;
use CTExport\Commands\ReportCommands\ReportBuilders\ReportBuilder;
use CTExport\Commands\Traits\LoadGroups;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

#[AsCommand(
    name: 'report:group-hierarchy',
    description: 'Report hierarchy of group.',
    hidden: false,
)]
class ReportGroupHierarchyCommand extends ReportCommand
{
    use LoadGroups;

    const ARGUMENT_PARENT_GROUP_ID = "ParentGroupId";

    protected function configure()
    {
        parent::configure();
        $this->addArgument(self::ARGUMENT_PARENT_GROUP_ID, InputArgument::REQUIRED, "Group Id of parent group.");

    }

    protected function getReportBuilder(InputInterface $input): ReportBuilder
    {
        $parentGroupId = $input->getArgument(self::ARGUMENT_PARENT_GROUP_ID);
        return new GroupHierarchyReport($this->loadGroupById((int)$parentGroupId));
    }
}