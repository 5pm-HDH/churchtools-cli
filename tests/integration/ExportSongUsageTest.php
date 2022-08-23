<?php


use CTExport\ChurchToolsCliApplication;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class ExportSongUsageTest extends TestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $application = ChurchToolsCliApplication::create();
        $command = $application->find('export:song-usage');
        $this->commandTester = new CommandTester($command);
    }

    public function testInvalidDate()
    {
        // start-date is after end-date
        $this->commandTester->execute(["calendar_ids" => "54", "--start_date" => "2022-07-01", "--end_date" => "2022-01-01"]);
        $this->assertEquals($this->commandTester->getStatusCode(), Command::INVALID);
    }

    public function testInvalidCalendarList()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->commandTester->execute(["calendar_ids" => "wrongList"]);
    }
}