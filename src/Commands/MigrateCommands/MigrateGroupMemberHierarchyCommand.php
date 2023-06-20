<?php


namespace CTExport\Commands\MigrateCommands;


use CTApi\Models\Group;
use CTExport\Commands\Traits\LoadGroups;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
    const INPUT_OPTION_REMOVE_REDUNDANT_PARENT_MEMBERS = "remove-redundant-parent-members";

    private Group $parentGroup;
    private bool $removeRedundantParentMembers = false;

    protected function configure()
    {
        parent::configure();
        $this->addArgument(self::ARGUMENT_PARENT_GROUP_ID, InputArgument::REQUIRED, "Group Id of parent group.");
        $this->addArgument(self::ARGUMENT_CHILD_GROUP_IDS, InputArgument::REQUIRED, "Group Ids list of child groups.");
        $this->addOption(self::INPUT_OPTION_REMOVE_REDUNDANT_PARENT_MEMBERS, null, InputOption::VALUE_NEGATABLE, "Remove redundant parent members.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Add Context to Command
        $parentGroupId = $input->getArgument(self::ARGUMENT_PARENT_GROUP_ID);
        $this->parentGroup = $this->loadGroupById((int)$parentGroupId);
        $this->removeRedundantParentMembers = (bool)$input->getOption(self::INPUT_OPTION_REMOVE_REDUNDANT_PARENT_MEMBERS);
        return parent::execute($input, $output);
    }

    protected function collectModels(InputInterface $input): array
    {
        $childGroupIds = $this->getArgumentAsIntegerList($input, self::ARGUMENT_CHILD_GROUP_IDS);
        return $this->loadGroupsByIds($childGroupIds);
    }

    protected function getMigration(): Migration
    {
        return new GroupMemberHierarchyMigration($this->parentGroup, $this->removeRedundantParentMembers);
    }
}