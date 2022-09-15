<?php


namespace CTExport\Commands\ShowCommands;


use CTExport\Commands\Traits\LoadAbsence;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;


#[AsCommand(
    name: 'show:absence',
    description: 'Show absence of person.',
    hidden: false,
    aliases: ['show:absences']
)]
class ShowAbsencesCommand extends ShowTableCommand
{
    use LoadAbsence;

    const PERSON_ID = "person-id";

    protected function configure()
    {
        parent::configure();
        $this->addArgument(self::PERSON_ID, null, "Id of person to show absence.");
        $this->addOptionStartDate();
        $this->addOptionEndDate();
    }

    protected function getTableBuilder(InputInterface $input): TableBuilder
    {
        $personId = $input->getArgument(self::PERSON_ID);
        $startDate = $this->getOptionStartDate($input);
        $endDate = $this->getOptionEndDate($input);

        return TableBuilder::forAbsences($this->loadAbsence($personId, $startDate, $endDate));
    }
}