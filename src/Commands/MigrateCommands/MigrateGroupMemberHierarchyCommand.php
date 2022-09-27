<?php


namespace CTExport\Commands\MigrateCommands;


use CTApi\Models\Group;
use CTExport\Commands\Traits\LoadGroups;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'migrate:group-member-hierarchy',
    description: 'Add all member of child-groups to parent-group.',
    hidden: false,
)]
class MigrateGroupMemberHierarchyCommand extends MigrateCommand
{
    use LoadGroups;

    const ARGUMENT_CHILD_GROUP_IDS = "ChildGroupIds";
    const ARGUMENT_PARENT_GROUP_ID = "ParentGroupId";

    private Group $parentGroup;

    protected function configure()
    {
        parent::configure();
        $this->addArgument(self::ARGUMENT_PARENT_GROUP_ID, InputArgument::REQUIRED, "Group Id of parent group.");
        $this->addArgument(self::ARGUMENT_CHILD_GROUP_IDS, InputArgument::REQUIRED, "Group Ids list of child groups.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Add Context to Command
        $parentGroupId = $input->getArgument(self::ARGUMENT_PARENT_GROUP_ID);
        $this->parentGroup = $this->loadGroupById((int)$parentGroupId);
        return parent::execute($input, $output);
    }

    protected function collectModels(InputInterface $input): array
    {
        $childGroupIds = $this->getArgumentAsIntegerList($input, self::ARGUMENT_CHILD_GROUP_IDS);
        return $this->loadGroupsByIds($childGroupIds);
    }

    protected function getMigration(): Migration
    {
        return new GroupMemberHierarchyMigration($this->parentGroup);
    }
}