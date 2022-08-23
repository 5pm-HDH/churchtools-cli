<?php


namespace CTExport\Commands\Collections;


use PhpOffice\PhpSpreadsheet\Style\Border;

abstract class SpreadsheetBuilder
{

    /**
     * Build Spreadsheet
     */
    public abstract function build(string $filePathWithName): void;

    protected static function getStyleHeading(): array
    {
        return [
            "font" => [
                "bold" => true
            ],
            "borders" => [
                "allBorders" => ["borderStyle" => Border::BORDER_THIN],
                "outline" => ["borderStyle" => Border::BORDER_THICK]

            ]
        ];
    }

    protected function getStyleTable()
    {
        return [
            "borders" => [
                "allBorders" => ["borderStyle" => Border::BORDER_THIN],
                "outline" => ["borderStyle" => Border::BORDER_THICK]
            ]
        ];
    }
}