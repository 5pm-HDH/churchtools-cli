<?php


namespace CTExport\Commands\ShowCommands;


use CTApi\CTClient;
use CTExport\Commands\Traits\LoadPersons;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Tests\Unit\HttpMock\CTClientMock;


#[AsCommand(
    name: 'show:birthdays',
    description: 'Show Birthdays of next 3 month.',
    hidden: false,
)]
class ShowBirthdaysCommand extends ShowTableCommand
{
    use LoadPersons;

    const OPTION_GROUP_IDS = "group-ids";
    const OPTION_MY_GROUPS = "my-groups";

    protected function configure()
    {
        parent::configure();
        $this->addOptionStartDate("today");
        $this->addOptionEndDate("+ 3 months");
        $this->addOption(self::OPTION_GROUP_IDS, null, InputOption::VALUE_REQUIRED);
        $this->addOption(self::OPTION_MY_GROUPS, null, InputOption::VALUE_NONE);
    }

    protected function getTableBuilder(InputInterface $input): TableBuilder
    {
        $startDate = $this->getOptionStartDate($input);
        $endDate = $this->getOptionEndDate($input);
        $groupIds = $this->getOptionAsIntegerList($input, self::OPTION_GROUP_IDS);
        $myGroups = $input->getOption(self::OPTION_MY_GROUPS);

        if (!empty($groupIds)) {
            return TableBuilder::forBirthdays($this->loadBirthdaysForGroups($startDate, $endDate, $groupIds));
        } else {
            if ($myGroups == true) {
                return TableBuilder::forBirthdays($this->loadBirthdaysForMyGroups($startDate, $endDate));
            } else {
                return TableBuilder::forBirthdays($this->loadBirthdays($startDate, $endDate));
            }
        }
    }
}