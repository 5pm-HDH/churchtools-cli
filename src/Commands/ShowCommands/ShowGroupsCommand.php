<?php


namespace CTExport\Commands\ShowCommands;


use CTExport\Commands\Traits\LoadGroups;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;


#[AsCommand(
    name: 'show:groups',
    description: 'Show groups.',
    hidden: false,
)]
class ShowGroupsCommand extends ShowTableCommand
{
    use LoadGroups;

    const OPTION_MY_GROUPS = "my-groups";

    protected function configure()
    {
        parent::configure();
        $this->addOption(self::OPTION_MY_GROUPS, null, InputOption::VALUE_NONE, "Show only groups im member of.");
    }

    protected function getTableBuilder(InputInterface $input): TableBuilder
    {
        $myGroups = $input->getOption(self::OPTION_MY_GROUPS);
        if ($myGroups) {
            return TableBuilder::forGroups($this->loadMyGroups());
        } else {
            return TableBuilder::forGroups($this->loadGroups());
        }
    }
}