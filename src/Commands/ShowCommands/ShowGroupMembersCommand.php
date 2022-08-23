<?php


namespace CTExport\Commands\ShowCommands;


use CTExport\Commands\Traits\LoadGroups;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;


#[AsCommand(
    name: 'show:group-members',
    description: 'Show all members of group.',
    hidden: false,
)]
class ShowGroupMembersCommand extends ShowTableCommand
{
    use LoadGroups;

    const ARGUMENT_GROUP_ID = "GroupId";

    protected function configure()
    {
        parent::configure();
        $this->addArgument(self::ARGUMENT_GROUP_ID, InputArgument::REQUIRED, "Group Id");
    }

    protected function getTableBuilder(InputInterface $input): TableBuilder
    {
        $groupIds = $input->getArgument(self::ARGUMENT_GROUP_ID);
        return TableBuilder::forGroupMembers($this->loadGroupMember($groupIds));
    }
}