<?php

use Yii;
use yii\console\ExitCode;

class RCloneSmokeCest
{


    /**
     * Make sure rclone could be called
     */
    public function listBucketId(\FunctionalTester $I) {
        $I->runShellCommand("rclone --config=scripts/.rclone.conf lsd test-cos-mac:");

        $output = $I->grabShellOutput();
        $I->assertStringContainsString('testrclonegznoversion-1306096270', $output, 'testrclonegznoversion-1306096270 is not found');
    }
}