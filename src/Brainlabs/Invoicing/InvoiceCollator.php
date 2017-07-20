<?PHP 

namespace Brainlabs\Invoicing;

use \PHPExcel;
use \PHPExcel_IOFactory;
use \PHPExcel_Cell;
use \PHPExcel_Worksheet;
use \PHPExcel_Writer_Excel2007;
use \Exception;

class InvoiceCollator 
{
    private $dir;

    private $headers;

    private $ignore;

    const SUMMARY = "Summary";

    const COLLATED = "Collated.xlsx";

    const COMMERCIALS = "Commercials";



    public function __construct($dir) 
    {
        if (substr($dir, -1) !== '/') $dir .= '/';

        if (!is_dir($dir)) {
            throw new Exception("Specified directory does not exist: $dir");
        }

        $this->dir = $dir;
        $this->ignore = [
            self::SUMMARY,
            self::SUMMARY . "_cache",
            self::COMMERCIALS
        ];
    }

    public function run()
    {
        // Iterate through the files in the directory
        $dir = $this->dir;
        $ignoreFiles = ['.', '..', self::COLLATED];
        foreach (array_diff(scandir($dir), $ignoreFiles) as $file) {
            // @todo Check filetype
            $data[] = $this->collateFile($dir . $file);
        }
        $this->outputCollated($data);
    }

    private function flush(PHPExcel $ss, $filepath) 
    {
        $writer = new PHPExcel_Writer_Excel2007($ss);
        $writer->save($filepath);
    }
    
    private function collateFile($filepath)
    {
        printf("Collating file : %s\n", $filepath);
        $ss = PHPExcel_IOFactory::load($filepath);

        $sheets = $ss->getAllSheets();
        $data = [];
        foreach ($sheets as $sheet) {
            if (!in_array($sheet->getTitle(), $this->ignore)) {
                $data[] = $this->getDataFromSheet($sheet);
            }
        }

        if (!count($data)) {
            $summarySheet = $ss->getSheetByName(self::SUMMARY);
            if (is_null($summarySheet)) {
                throw new Exception(
                    "No Client sheets or Summary sheet in $filepath"
                );
            }
            $data[] = $this->getDataFromSheet($summarySheet);
        }

        $summaryData = $this->createCollatedSheet($ss, $data, self::SUMMARY);

        $commsSheet = $ss->getSheetByName(self::COMMERCIALS);
        if (is_null($commsSheet)) {
            throw new Exception("No Commercials sheet in file : $filepath");
        }
        $commercialData = $this->getDataFromSheet($commsSheet);

        $this->flush($ss, $filepath);
        return [
            self::SUMMARY => $summaryData,
            self::COMMERCIALS => $commercialData
        ];
    }

    private function getDataFromSheet($sheet) 
    {
        printf("\tGetting data from sheet : %s\n", $sheet->getTitle());
        $highestRow = $sheet->getHighestDataRow();
        $highestColLetter = $sheet->getHighestDataColumn();
        $highestCol = PHPExcel_Cell::columnIndexFromString($highestColLetter);
        
        $data = [];
        if ($highestRow < 2) {
            return $data;
        }

        $headers = [];
        for ($i = 0; $i < $highestCol; $i++) {
            $headers[] = $this->getValueAtCell($sheet, $i, 1);
        }

        $saved = $this->headers;
        if (is_null($saved) || count($headers) > count($saved)) {
            $this->headers = $headers;
        }

        for ($i = 2; $i  < $highestRow+1; $i++) {
            $row = [];
            $empty = true;
            for ($h = 0; $h < $highestCol; $h++) {
                $value = trim($this->getValueAtCell($sheet, $h, $i));
                $key =$headers[$h]; 
                if ($key !== '') $row[$key] = $value;
                if ($empty && $value != '') $empty = false;
            }
            if (!$empty) $data[] = $row;
        }
        return $data;
    }

    private function createCollatedSheet($ss, $data, $sheetName) 
    {
        $summaryName = self::SUMMARY;
        $summary = $ss->getSheetByName($summaryName);
        if (!is_null($summary)) {
            $this->cacheSheet($ss, $summary);
        }
        $summary = $this->clearSheet($ss, $summaryName);

        $output = call_user_func_array('array_merge', $data);
        $this->writeDataToSheet($summary, $output, 1, 0);
        return $output;
    }

    private function cacheSheet($ss, $sheet) 
    {
        $cacheName = $sheet->getTitle() . "_cache";
        $cache = $this->clearSheet($ss, $cacheName);
        $this->writeDataToSheet($cache, $this->getDataFromSheet($sheet), 1, 0);
        $cache->setSheetState(PHPExcel_Worksheet::SHEETSTATE_HIDDEN);
    }

    private function clearSheet($ss, $sheetName) 
    {
        $sheet = $ss->getSheetByName($sheetName);
        if (!is_null($sheet)) {
            $ss->removeSheetByIndex($ss->getIndex($sheet));
        }

        $sheet = $ss->createSheet();
        $sheet->setTitle($sheetName);
        return $sheet;
    }

    private function outputCollated($data) 
    {
        $filepath = $this->dir . self::COLLATED;
        printf("Creating Collated File : %s\n", $filepath);
        $ss = new PHPExcel();

        $summarySheet = $ss->getActiveSheet();
        $summarySheet->setTitle(self::SUMMARY);

        $commsSheet = $ss->createSheet();
        $commsSheet->setTitle(self::COMMERCIALS);

        $sheets = [
            self::SUMMARY=>$summarySheet,
            self::COMMERCIALS=>$commsSheet
        ];

        $output = [self::SUMMARY=>[], self::COMMERCIALS=>[]];
        foreach ($data as $sheetData) {
            foreach ($sheetData as $key => $data) {
                $output[$key] = array_merge($output[$key], $data);
            }
        }

        foreach ($output as $sheetName => $data) {
            $this->setHeadersFromData($data);
            $this->writeDataToSheet($sheets[$sheetName], $data, 1, 0);
        }

        $this->flush($ss, $filepath);
    }
    
    private function setHeadersFromData($data) {
        $headerStore = [];
        foreach ($data as $row) {
            foreach ($row as $header => $value) {
                $headerStore[$header] = true;
            }
        }
        $this->headers = array_keys($headerStore);
    }

    private function writeDataToSheet($sheet, $data, $startRow, $startCol) 
    {
        printf("\tWriting data to sheet : %s\n", $sheet->getTitle());
        $headers = $this->headers;
        if (!is_array($headers)) {
            throw new Exception("Headers not set");
        }
        foreach ($headers as $i => $header) {
            $column = PHPExcel_Cell::stringFromColumnIndex($i);
            $coord = $column . "1";
            $sheet->setCellValue($coord, $header);
            $sheet->getStyle($coord)->getFont()->setBold(true);
        }

        if (count($data) && count($data[0])) {
            $r = $startRow+1;
            $c = $startCol;
            foreach ($data as $row) {
                foreach ($headers as $header) {
                    $column = PHPExcel_Cell::stringFromColumnIndex($c++);
                    $val = (isset($row[$header]) ? $row[$header] : '');
                    $sheet->setCellValue($column . $r, $val);
                }
                $c = $startCol;
                $r++;
            }
        }
    }

    /**
     * @param PHPExcel_Worksheet $sheet
     * @param int $col
     * @param int $row
     * @return string
     */
    private function getValueAtCell($sheet, $col, $row)
    {
        return (string) $sheet->getCellByColumnAndRow($col, $row)->getValue();
    }
}
