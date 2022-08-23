<?php


namespace CTExport\Commands\Collections;

use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SpreadsheetBuilder
{
    private array $data;

    private bool $showDataColumns = false;
    private bool $showCountColumn = false;
    private bool $doFlipAxes = false;

    /**
     * SpreadsheetBuilder constructor. Data consists of array with String key as identifier an array list of strings as
     * values.
     * <code>
     * [
     *    "Event A" => ["Matthew", "John"],
     *    "Event B" => ["John", "Paul"]
     * ]
     * </code>
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Add Count-Column to Spreadsheet, that counts the number of Data for each row.
     * @return $this fluent-api
     */
    public function withCountColumn(): SpreadsheetBuilder
    {
        $this->showCountColumn = true;
        return $this;
    }

    /**
     * Show the Data-Columns for each row.
     * @return $this fluent-api
     */
    public function withDataColumns(): SpreadsheetBuilder
    {
        $this->showDataColumns = true;
        return $this;
    }

    /**
     * Switch flip columns to become rows and rows to columns.
     * @return $this fluent-api
     */
    public function doFlipAxes(): SpreadsheetBuilder
    {
        $this->doFlipAxes = true;
        return $this;
    }

    /**
     * Build Spreadsheet
     */
    public function build(string $filePathWithName): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        if ($this->doFlipAxes) {
            $this->data = $this->flipAxesOfData($this->data);
        }

        // Create First-Column
        $dataColumnHeadings = [];
        $row = 2;
        foreach ($this->data as $key => $dataArray) {
            $sheet->setCellValueByColumnAndRow(1, $row, $key);
            $row += 1;
            if ($this->showDataColumns) { // Only fill Column-Heading if Show Data Columns
                foreach ($dataArray as $dataValue) {
                    if (!in_array($dataValue, $dataColumnHeadings)) {
                        $dataColumnHeadings[] = $dataValue;
                    }
                }
            }
        }

        // Create Data-Columns
        if ($this->showDataColumns) {
            // Insert Column-Headings
            $column = 2;
            foreach ($dataColumnHeadings as $columnHeading) {
                $sheet->setCellValueByColumnAndRow($column, 1, $columnHeading);
                $column += 1;
            }

            // Insert Column-Data
            $row = 2;
            foreach ($this->data as $dataArray) {
                foreach ($dataArray as $dataValue) {
                    $cellIndexColumn = array_search($dataValue, $dataColumnHeadings) + 2;
                    $sheet->setCellValueByColumnAndRow($cellIndexColumn, $row, "X");
                }
                $row += 1;
            }
        }

        // Create Count-Column
        if ($this->showCountColumn) {
            try {
                $sheet->insertNewColumnBefore("B");

                $sheet->setCellValueByColumnAndRow(2, 1, "Anzahl");

                $row = 2;
                foreach ($this->data as $key => $dataArray) {
                    $sheet->setCellValueByColumnAndRow(2, $row, sizeof($dataArray));
                    $row += 1;
                }
            } catch (Exception $e) {
                var_dump($e);
                // ignore
            }
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePathWithName);
    }

    private function flipAxesOfData(array $data): array
    {
        $newData = [];

        foreach ($data as $key => $dataRow) {

            foreach ($dataRow as $dataPoint) {

                if (!array_key_exists($dataPoint, $newData)) {
                    $newData[$dataPoint] = [];
                }

                $newData[$dataPoint][] = $key;
            }
        }
        return $newData;
    }
}