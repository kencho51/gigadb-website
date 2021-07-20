<?php

use Yii;
use yii\console\ExitCode;

class FixPermissionCest
{
    /**
     * list file with permission not ok
     */
    public function listNotOkFilePermissions (\FunctionalTester $I) {
        $I->runShellCommand("ls -al /app/tests/_data/permissions/100001_101009/100300/perm-not-ok.txt");
        $output = $I->grabShellOutput();
//        file_put_contents('test-shell.txt', print_r($output, true));
        $I->assertStringContainsString("----------", $output, "Not Ok file cannot be ls");
    }
}