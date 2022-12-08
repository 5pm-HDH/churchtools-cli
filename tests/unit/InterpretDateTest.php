<?php


namespace Tests\Unit;


use CTExport\Commands\AbstractCommand;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class InterpretDateTest extends TestCase
{

    private MockCommand $command;

    protected function setUp(): void
    {
        $this->command = new MockCommand();
        parent::setUp();
    }


    private function assertDateInterpret($inputDate, $outputDate)
    {
        $castedDate = $this->command->exposedCastDate($inputDate);
        $this->assertEquals($outputDate, $castedDate);
    }

    private function assertDateException($inputDate)
    {
        $exceptionThrown = false;
        try {
            $this->command->exposedCastDate($inputDate);
        } catch (InvalidArgumentException $exception) {
            $exceptionThrown = true;
        } finally {
            $this->assertTrue($exceptionThrown, "No exception has been thrown by input date: " . $inputDate);
        }
    }

    public function testInterpretDateFormat()
    {
        $this->assertDateInterpret("2020-01-02", "2020-01-02");
        $this->assertDateException("2020-13-02");
    }

    public function testInterpretMonth()
    {
        $dateFormat = date("Y-m-d", strtotime("-1 month"));
        $this->assertDateInterpret("-1 month", $dateFormat);
    }

}

final class MockCommand extends AbstractCommand
{
    public function exposedCastDate(string $inputString): string
    {
        return $this->castDate($inputString);
    }
}