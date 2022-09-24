<?php


namespace CTExport\Commands\Collections;


class MarkdownBuilder
{
    private $markdownContent = []; // markdown content as array

    private function prepend(string $text)
    {
        array_unshift($this->markdownContent, $text);
    }

    public function addHeading(string $heading): MarkdownBuilder
    {
        $this->markdownContent[] = "# " . $heading . "\n\n";
        return $this;
    }

    public function prependHeading(string $heading): MarkdownBuilder
    {
        $this->prepend("# " . $heading . "\n\n");
        return $this;
    }

    public function addSubHeading(string $subheading): MarkdownBuilder
    {
        $this->markdownContent[] = "## " . $subheading . "\n\n";
        return $this;
    }

    public function addSubSubHeading(string $subheading): MarkdownBuilder
    {
        $this->markdownContent[] = "### " . $subheading . "\n\n";
        return $this;
    }

    public function addText(string $text): MarkdownBuilder
    {
        $this->markdownContent[] = $text . "\n";
        return $this;
    }

    public function prependText(string $text): MarkdownBuilder
    {
        $this->prepend($text . "\n");
        return $this;
    }

    public function addBoldText(string $boldText): MarkdownBuilder
    {
        $this->markdownContent[] = "**" . $boldText . "**\n";
        return $this;
    }

    public function addListItem(string $listItem): MarkdownBuilder
    {
        $this->markdownContent[] = " - " . $listItem . "\n";
        return $this;
    }

    public function prependListItem(string $listItem): MarkdownBuilder
    {
        $this->prepend(" - " . $listItem . "\n");
        return $this;
    }

    public function addNewLine(): MarkdownBuilder
    {
        $this->markdownContent[] = "\n";
        return $this;
    }

    public function sortMarkdown(): MarkdownBuilder
    {
        sort($this->markdownContent);
        return $this;
    }

    public function build(string $filePath)
    {
        file_put_contents($filePath, implode("", $this->markdownContent));
    }

    public static function clone(MarkdownBuilder $markdownBuilder): MarkdownBuilder
    {
        $clonedMarkdownContent = array_merge($markdownBuilder->markdownContent);
        $clonedMarkdownBuilder = new MarkdownBuilder();
        $clonedMarkdownBuilder->markdownContent = $clonedMarkdownContent;
        return $clonedMarkdownBuilder;
    }
}