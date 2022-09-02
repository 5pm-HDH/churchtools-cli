<?php


namespace CTExport\Commands\Collections;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SpreadsheetTableBuilder extends SpreadsheetBuilder
{

    public function __construct(
        private array $headings,
        private array $rows)
    {

    }

    public function build(string $filePathWithName): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set Headings
        $columnIndex = 1;
        foreach ($this->headings as $heading) {
            $sheet->setCellValueByColumnAndRow($columnIndex, 1, $heading);
            $columnIndex++;
        }
        $sheet->getStyleByColumnAndRow(1, 1, sizeof($this->headings), 1)->applyFromArray(parent::getStyleHeading());

        // Set Table-Rows
        $rowIndex = 2;
        foreach ($this->rows as $row) {
            $columnIndex = 1;
            foreach ($row as $cellValue) {
                $sheet->setCellValueByColumnAndRow($columnIndex, $rowIndex, $cellValue);
                $sheet->getColumnDimensionByColumn($columnIndex)->setAutoSize(true);
                $columnIndex++;
            }
            $rowIndex++;
        }
        $sheet->getStyleByColumnAndRow(1, 2, $columnIndex - 1, $rowIndex - 1)->applyFromArray(parent::getStyleTable());


        $writer = new Xlsx($spreadsheet);
        $writer->save($filePathWithName);
    }

    public static function fromJsonArray(array $objectArray): SpreadsheetTableBuilder
    {
        $lastObject = end($objectArray);
        $heading = array_keys($lastObject);
        $rows = array_map(function($object){
            return array_values($object);
        }, $objectArray);

        return new SpreadsheetTableBuilder($heading, $rows);
    }
}