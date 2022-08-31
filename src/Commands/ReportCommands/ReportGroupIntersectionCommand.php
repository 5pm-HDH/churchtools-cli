<?php

namespace CTExport\Commands\ReportCommands;

use CTExport\Commands\ReportCommands\ReportBuilders\GroupIntersectionReport;
use CTExport\Commands\ReportCommands\ReportBuilders\ReportBuilder;
use CTExport\Commands\Traits\LoadGroups;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

#[AsCommand(
    name: 'report:group-intersection',
    description: 'Report intersection of group members.',
    hidden: false,
)]
class ReportGroupIntersectionCommand extends ReportCommand
{
    use LoadGroups;

    const ARGUMENT_CHILD_GROUP_IDS = "ChildGroupIds";
    const ARGUMENT_PARENT_GROUP_ID = "ParentGroupId";

    protected function configure()
    {
        parent::configure();
        $this->addArgument(self::ARGUMENT_PARENT_GROUP_ID, InputArgument::REQUIRED, "Group Id of parent group.");
        $this->addArgument(self::ARGUMENT_CHILD_GROUP_IDS, InputArgument::REQUIRED, "Group Ids list of child groups.");
    }

    protected function getReportBuilder(InputInterface $input): ReportBuilder
    {
        $parentGroupId = $input->getArgument(self::ARGUMENT_PARENT_GROUP_ID);
        $childGroupIds = $this->getArgumentAsIntegerList($input, self::ARGUMENT_CHILD_GROUP_IDS);

        $parentGroup = $this->loadGroupById((int)$parentGroupId);
        $childGroups = $this->loadGroupsByIds($childGroupIds);

        return new GroupIntersectionReport($parentGroup, $childGroups);
    }
}