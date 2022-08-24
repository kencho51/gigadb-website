<?php

namespace common\models;

use PhpOffice\PhpSpreadsheet\Reader\Csv;
use \Yii;
use yii\queue\Queue;

class EMReportJob extends \yii\base\BaseObject implements \yii\queue\JobInterface
{

    public string $content;
    public string $effectiveDate ;
    public string $fetchDate ;
    public string $scope;


    public function execute($queue)
    {
        if ($this->scope === "manuscripts") {
            $this->executeManuscriptJob($this->content);
        }
            //TODO: Will be implemented in ticket no. #1065
//            $ingest = Ingest::findOne(["report_type" => "1", "parse_status" => null]);
//            $ingest->remote_file_status = Ingest::REMOTE_FILES_STATUS_EXISTS;
//            $ingest->parse_status = Ingest::PARSE_STATUS_YES;
//            $ingest->update();

//        }
            //TODO: Will be implemented in ticket no. #1065
//        elseif ($this->scope === "manuscripts" && $this->content === "No Results") {
//            $ingest = Ingest::findOne(["report_type" => "1", "parse_status" => null]);
//            $ingest->remote_file_status = Ingest::REMOTE_FILES_STATUS_NO_RESULTS;
//            $ingest->parse_status = Ingest::PARSE_STATUS_NO;
//            $ingest->update();
//        }
    }

    /**
     * Create manuscript instances from the queue content and save then to the manuscript table
     *
     * @param string $content
     * @return void
     */
    public function executeManuscriptJob(string $content): void
    {
        if ($content !== "No Results") {
            //Step 1: Put queue content to csv
            $tempManuscriptCsvFile = tempnam(sys_get_temp_dir(), "test-manuscripts").".csv";
            file_put_contents($tempManuscriptCsvFile, $content);

            //Step 2: Parse the csv
            $reportData = self::parseReport($tempManuscriptCsvFile);

            //Step 3: Create manuscript instance
            $manuscriptInstances = Manuscript::createInstancesFromEmReport($reportData);

            //Step 4: Save content to table
            $this->storeManuscripts($manuscriptInstances);
        }
    }

    /**
     * Create an associative array from the csv file
     *
     * @param $emReportPath
     * @return array
     */

    public static function parseReport($emReportPath): array
    {
        $reportData = [];

        $reader = new Csv();
        $spreadsheet = $reader->load($emReportPath);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $columnHeader = str_replace(' ', '_', array_map('strtolower', array_shift($sheetData)));
        foreach ($sheetData as $row) {
            $reportData[] = array_combine($columnHeader,$row);
        }
        return $reportData;
    }

    /**
     * Store manuscript object to its table
     *
     * @param array Manuscript[]
     * @return bool
     */
    public function storeManuscripts(array $manuscripts): bool
    {
        $storeStatus = 0;
        foreach ($manuscripts as $manuscript) {
            if ($manuscript->save()) {
                $storeStatus = 1;
            }
        }
        return $storeStatus;
    }
}
