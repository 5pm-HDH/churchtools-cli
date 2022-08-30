<?php


namespace CTExport\Commands\Collections;

use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SpreadsheetDataBuilder extends SpreadsheetBuilder
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
    public function withCountColumn(): SpreadsheetDataBuilder
    {
        $this->showCountColumn = true;
        return $this;
    }

    /**
     * Show the Data-Columns for each row.
     * @return $this fluent-api
     */
    public function withDataColumns(): SpreadsheetDataBuilder
    {
        $this->showDataColumns = true;
        return $this;
    }

    /**
     * Switch flip columns to become rows and rows to columns.
     * @return $this fluent-api
     */
    public function doFlipAxes(): SpreadsheetDataBuilder
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
        $sheet->getStyleByColumnAndRow(1, 2, 1, $row - 1)->applyFromArray(parent::getStyleHeading());
        $sheet->getColumnDimensionByColumn(1)->setAutoSize(true);

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
                    $cellIndexColumn = ( (int) array_search($dataValue, $dataColumnHeadings)) + 2;
                    $sheet->setCellValueByColumnAndRow($cellIndexColumn, $row, "X");
                    $sheet->getColumnDimensionByColumn($cellIndexColumn)->setWidth(5);
                }
                $row += 1;
            }
            $sheet->getStyleByColumnAndRow(2, 2, $column - 1, $row - 1)->applyFromArray(parent::getStyleTable());
            $sheet->getStyleByColumnAndRow(2, 1, $column - 1, 1)->applyFromArray(parent::getStyleHeading());
        }

        // Create Count-Column
        if ($this->showCountColumn) {
            try {
                $sheet->insertNewColumnBefore("B");

                $sheet->setCellValueByColumnAndRow(2, 1, "Anzahl");

                $row = 2;
                foreach ($this->data as $key => $dataArray) {
                    $sheet->setCellValueByColumnAndRow(2, $row, sizeof(array_unique($dataArray)));
                    $sheet->getColumnDimensionByColumn(2)->setAutoSize(true);
                    $row += 1;
                }
            } catch (Exception $e) {
                // ignore
            }
            $sheet->getStyleByColumnAndRow(2, 2, 2, $row - 1)->applyFromArray(parent::getStyleTable());
            $sheet->getStyleByColumnAndRow(2, 1, 2, 1)->applyFromArray(parent::getStyleHeading());
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