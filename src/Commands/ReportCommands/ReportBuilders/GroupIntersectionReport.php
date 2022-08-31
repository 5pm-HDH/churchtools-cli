<?php


namespace CTExport\Commands\ReportCommands\ReportBuilders;


use CTApi\Exceptions\CTRequestException;
use CTApi\Models\Group;
use CTApi\Models\Person;
use CTApi\Requests\PersonRequest;
use CTExport\Commands\Collections\MarkdownBuilder;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class GroupIntersectionReport implements ReportBuilder
{
    private bool $showProgressBar = false;
    private array $member = [];

    public function __construct(
        private Group $parentGroup,
        private array $childGroups
    )
    {
    }

    public function withProgressBar(): ReportBuilder
    {
        $this->showProgressBar = true;
        return $this;
    }

    public function build(OutputInterface $output, string $outputPath)
    {
        $progressBar = null;
        if ($this->showProgressBar) {
            $progressBar = new ProgressBar($output, sizeof($this->childGroups));
            $progressBar->start();
        }

        $md = new MarkdownBuilder();
        $md->addHeading("Group intersection report for " . $this->parentGroup->getName() . " (#" . $this->parentGroup->getId() . ")");
        $md->addSubHeading("1. Check if parent-group contains all members of child-groups.");

        $parentGroupMembers = $this->getMemberOfGroup($this->parentGroup, $output);
        $parentGroupMemberIdsThatAreNotInAnyChildGroup = array_map(function (Person $person) {
            return $person->getId();
        }, $parentGroupMembers);

        // PROCESS ALL CHILD-GROUPS
        foreach ($this->childGroups as $childGroup) {
            $this->processChildGroup($childGroup, $parentGroupMembers, $parentGroupMemberIdsThatAreNotInAnyChildGroup, $md, $output);
            if ($progressBar != null) {
                $progressBar->advance();
            }
        }

        $md->addSubHeading("2. Check if all parent-group members are in any child group.")
            ->addSubSubHeading("Members of parent-group, that cannot be found in any child group:")
            ->addBoldText("Missing in any child-group:")->addNewLine();
        if ($progressBar != null) {
           $progressBar->setMaxSteps(sizeof($this->childGroups) + sizeof($parentGroupMemberIdsThatAreNotInAnyChildGroup));
        }
        foreach ($parentGroupMemberIdsThatAreNotInAnyChildGroup as $personId) {
            $person = PersonRequest::find( (int) $personId);
            if ($person != null) {
                $md->addListItem($person->getFirstName() . " " . $person->getLastName() . " (#" . $person->getId() . ")");
            }
            if ($progressBar != null) {
                $progressBar->advance();
            }
        }

        $md->build($outputPath);
        if ($progressBar != null) {
            $progressBar->finish();
        }
        $output->writeln("");

        $output->writeln("Successfully stored report to: " . $outputPath);
    }

    /**
     * Retrieve members and convert them to Person-Objects
     * @param Group $group
     * @param OutputInterface $output
     * @return array Person-Array
     */
    private function getMemberOfGroup(Group $group, OutputInterface $output): array
    {
        $members = [];
        try {
            $members = $group->requestMembers()?->get() ?? [];
        } catch (CTRequestException) {
            // ignore
        }

        if (empty($members)) {
            $output->writeln("Could not find members for group: " . $group->getName() . " (#" . $group->getId() . ")");
        }

        $persons = [];
        foreach ($members as $groupMember) {
            try {
                $person = $groupMember->requestPerson();
                if ($person == null) {
                    throw new CTRequestException();
                }
                $persons[] = $person;
            } catch (CTRequestException) {
                $output->writeln("Could not retrieve person with id " . $groupMember->getPersonId());
            }
        }
        return $persons;
    }

    private function processChildGroup(
        Group $childGroup,
        array $parentGroupMembers,
        array &$parentGroupMemberIdsThatAreNotInAnyChildGroup,
        MarkdownBuilder $md,
        OutputInterface $output): void
    {
        $childGroupMembers = $this->getMemberOfGroup($childGroup, $output);

        $childGroupMemberIds = array_map(function (Person $person) {
            return $person->getId();
        }, $childGroupMembers);
        $parentGroupMemberIds = array_map(function (Person $person) {
            return $person->getId();
        }, $parentGroupMembers);

        $missingInParentGroup = [];

        foreach ($childGroupMembers as $childGroupMember) {
            if (!in_array($childGroupMember->getId(), $parentGroupMemberIds)) {
                $missingInParentGroup[] = $childGroupMember;
            }
        }

        $parentGroupMemberIdsThatAreNotInAnyChildGroup = array_filter($parentGroupMemberIdsThatAreNotInAnyChildGroup, function ($personId) use ($childGroupMemberIds) {
            return !in_array($personId, $childGroupMemberIds);
        });

        $md->addSubSubHeading("Intersection with " . $childGroup->getName() . " (#" . $childGroup->getId() . ")")
            ->addBoldText("Missing in parent-group:")->addNewLine();

        if (empty($missingInParentGroup)) {
            $md->addListItem("(no member found)");
        }

        foreach ($missingInParentGroup as $missingInParentGroupPerson) {
            $md->addListItem($missingInParentGroupPerson->getFirstName() . " " . $missingInParentGroupPerson->getLastName() . " (#" . $missingInParentGroupPerson->getId() . ")");
        }
        $md->addNewLine();
    }
}