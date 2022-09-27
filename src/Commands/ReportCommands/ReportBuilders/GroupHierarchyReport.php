<?php


namespace CTExport\Commands\ReportCommands\ReportBuilders;


use CTApi\Models\Group;
use CTExport\Commands\Collections\MarkdownBuilder;
use Symfony\Component\Console\Output\OutputInterface;

class GroupHierarchyReport implements ReportBuilder
{
    private bool $showProgressBar = false;

    public function __construct(
        private Group $parentGroup
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
        $markdown = new MarkdownBuilder();

        $markdown->addHeading("Group-Hierarchy Report");
        $markdown->addSubHeading("Group:");

        $markdown->addListItem("ID: " . $this->parentGroup->getId());
        $markdown->addListItem("GUID: " . $this->parentGroup->getGuid());
        $markdown->addListItem("Name: " . $this->parentGroup->getName());
        $markdown->addNewLine();

        $markdown->addSubHeading("Children-Groups:");
        $output->writeln("Process children-groups:");
        $this->loadAndLogChildrenGroup($this->parentGroup, $output, $markdown);
        $markdown->addNewLine();

        $markdown->addSubHeading("Parent-Groups:");
        $output->writeln("Process parent-groups:");
        $this->loadAndLogParentGroup($this->parentGroup, $output, $markdown);
        $markdown->addNewLine();

        $markdown->build($outputPath);

        $output->writeln("Successfully stored report to: " . $outputPath);
    }

    public function loadAndLogChildrenGroup(Group $group, OutputInterface $output, MarkdownBuilder $markdown, int $depth = 1)
    {
        $childrenGroups = $group->requestGroupChildren()?->get() ?? [];
        $groupIdentifier = $group->getName() . " (#" . $group->getId() . ")";

        $tabs = "";
        for ($i = 0; $i < $depth; $i++) {
            $tabs .= "\t";
        }
        $output->writeln($tabs . "-" . $groupIdentifier);
        $markdown->addListItem($groupIdentifier, $depth);

        foreach ($childrenGroups as $childGroup) {
            $this->loadAndLogChildrenGroup($childGroup, $output, $markdown, $depth + 1);
        }
    }

    public function loadAndLogParentGroup(Group $group, OutputInterface $output, MarkdownBuilder $markdown, int $depth = 1)
    {
        $parentGroups = $group->requestGroupParents()?->get() ?? [];
        $groupIdentifier = $group->getName() . " (#" . $group->getId() . ")";

        $tabs = "";
        for ($i = 0; $i < $depth; $i++) {
            $tabs .= "\t";
        }

        $output->writeln($tabs . "-" . $groupIdentifier);
        $markdown->addListItem($groupIdentifier, $depth);

        foreach ($parentGroups as $parentGroup) {
            $this->loadAndLogParentGroup($parentGroup, $output, $markdown, $depth + 1);
        }
    }
}