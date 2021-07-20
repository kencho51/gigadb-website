<?php

use Yii;
use yii\console\ExitCode;

class RCloneSmokeCest
{

//    /**
//     * List files/dir of the bucket
//     */
//    public function listEverythingInBucket(\FunctionalTester $I) {
//        $bucketId = "testrclonegznoversion-1306096270";
//        $I->runShellCommand("rclone --config=scripts/.rclone.conf ls test-cos-mac:".$bucketId."");
//
//
//    }
//
//    /**
//     * List Bucket dir
//     */
//    public function listBucketDir(\FunctionalTester $I) {
//        $bucketId = "testrclonegznoversion-1306096270";
//        $I->runShellCommand("rclone --config=scripts/.rclone.conf lsd test-cos-mac:".$bucketId."");
//
//        $output = $I->grabShellOutput();
//        $I->assertStringContainsString('test-upload-from-mac', $output, 'Dir is not found');
//    }
    /**
     * Create Bucket: dataset-1306096270
     * List Bucket ID
     */
    public function listBucketId(\FunctionalTester $I) {
        $I->runShellCommand("rclone --config=scripts/.rclone.conf lsd test-cos-mac:");

        $output = $I->grabShellOutput();
        $I->assertStringContainsString("testrclonegznoversion-1306096270", $output, "testrclonegznoversion-1306096270 is not found");
        $I->assertStringContainsString("dataset-1306096270", $output, "dataset-1306096270 is not found");
    }

    /**
     * Dry run the backup process
     */
    public function dryRunSyncBackup(\FunctionalTester $I) {
        $I->runShellCommand("rclone --config=scripts/.rclone.conf sync --dry-run tests/_data/dataset1 test-cos-mac:dataset-1306096270/");

        $output = $I->grabShellOutput();
        file_put_contents('test-shell.txt', print_r($output, true));
//        $I->assertStringContainsString("test.csv: Skipped copy as --dry-run is set", $tokens[0], "test.csv could not be sync");
//        $I->assertStringContainsString("test.tsv: Skipped copy as --dry-run is set", $tokens[1], "test.tsv could not be sync");
//        $I->assertStringContainsString("readme_dataset.txt: Skipped copy as --dry-run is set", $tokens[2], "readme_dataset.txt could not be sync");
    }

    /**
     * @param String $directory
     * @return mixed
     */
    private function listBucketDirectory(\FunctionalTester $I, $directory) {
        $I->runShellCommand("rclone --config=scripts/.rclone.conf ls test-cos-mac:".$directory." ");
        return $I->grabShellOutput();
    }

    /**
     * @param String $filepath
     * @return mixed
     */
    private function getBucketFileInfo(\FunctionalTester $I, $filepath) {
        $I->runShellCommand("rclone --config=scripts/.rclone.conf info ".$filepath." 2>&1");
        return $I->grabShellOutput();
    }

    /**
     * Try to sync dataset1 to remote dataset1
     */
    public function syncDataset1ToRemote(\FunctionalTester $I) {
        $I->runShellCommand("rclone --config=scripts/.rclone.conf sync tests/_data/dataset1 test-cos-mac:dataset-1306096270/");

        $output = $this->listBucketDirectory($I,"dataset-1306096270/");
        $tokens = preg_split('/\s+/', trim($output));
//        file_put_contents('test-shell.txt', print_r($tokens, true));
        $I->assertEquals("readme_dataset.txt", $tokens[1], "readme_dataset.txt file does not appear to have been uploaded");
        $I->assertEquals("test.csv", $tokens[3], "test.csv file does not appear to have been uploaded");
        $I->assertEquals("test.tsv", $tokens[5], "test.tsv file does not appear to have been uploaded");
    }
}