<?php

class ReadmeCest
{
    /**
     * Teardown code that is run after each test
     * 
     * Currently just removes the readme file for dataset DOI 100142.
     * 
     * @return void
     */
    public function _after()
    {
        if (file_exists("/home/curators/readme_100004.txt")) {
            unlink("/home/curators/readme_100004.txt");
        }
        if (file_exists("/home/curators/readme_100003.txt")) {
            unlink("/home/curators/readme_100003.txt");
        }
    }

    /**
     * Test create readme file for 100004 dataset
     *
     * @param FunctionalTester $I
     */
    public function tryCreate(FunctionalTester $I)
    {
        $I->cantSeeInDatabase("file", ["id" => 6300, "dataset_id" => 212, "name" => "readme_100004.txt", "size" => 1997, "location" => "https://s3.ap-northeast-1.wasabisys.com/gigadb-datasets/dev/pub/10.5524/100001_101000/100004/readme_100004.txt", "extension" => "txt"]);
        $I->cantSeeInDatabase("file_attributes", ["file_id" => 6300, "attribute_id" => 605, "value" => "60da98a0c5cb6f872a4576f737089636"]);
        $I->runShellCommand("/app/yii_test readme/create --doi 100004 --outdir=/home/curators --bucketPath wasabi:gigadb-datasets/dev/pub/10.5524");
        $I->seeInShellOutput("[DOI] 10.5524/100004");
        $I->runShellCommand("ls /home/curators");
        $I->seeInShellOutput("readme_100004.txt");
        $I->canSeeInDatabase("file", ["id" => 6300, "dataset_id" => 212, "name" => "readme_100004.txt", "size" => 1997, "location" => "https://s3.ap-northeast-1.wasabisys.com/gigadb-datasets/dev/pub/10.5524/100001_101000/100004/readme_100004.txt", "extension" => "txt"]);
        $I->canSeeInDatabase("file_attributes", ["file_id" => 6300, "attribute_id" => 605, "value" => "60da98a0c5cb6f872a4576f737089636"]);
    }

    /**
     * Test update existing readme file for 100003 dataset
     *
     * @param FunctionalTester $I
     */
    public function tryUpdate(FunctionalTester $I)
    {
        $I->canSeeInDatabase("file", ["id" => 88266, "dataset_id" => 211, "name" => "readme.txt", "size" => 1, "location" => "ftp://climb.genomics.cn/pub/10.5524/100001_101000/100003/readme.txt", "extension" => "txt"]);
        $I->cantSeeInDatabase("file_attributes", ["file_id" => 88266, "attribute_id" => 605, "value" => "e22f66cbedb5c8c913c0bcf495a4ae11"]);
        $I->runShellCommand("/app/yii_test readme/create --doi 100003 --outdir=/home/curators --bucketPath wasabi:gigadb-datasets/dev/pub/10.5524");
        $I->seeInShellOutput("[DOI] 10.5524/100003");
        $I->runShellCommand("ls /home/curators");
        $I->seeInShellOutput("readme_100003.txt");
        $I->canSeeInDatabase("file", ["id" => 88266, "dataset_id" => 211, "name" => "readme_100003.txt", "size" => 1889, "location" => "https://s3.ap-northeast-1.wasabisys.com/gigadb-datasets/dev/pub/10.5524/100001_101000/100003/readme_100003.txt", "extension" => "txt"]);
        $I->canSeeInDatabase("file_attributes", ["file_id" => 88266, "attribute_id" => 605, "value" => "e22f66cbedb5c8c913c0bcf495a4ae11"]);
    }

    /**
     * Test functionality using a DOI for a dataset that does not exist
     *
     * @param FunctionalTester $I
     */
    public function tryCreateWithBadDoi(FunctionalTester $I)
    {
        # Test actionCreate function in ReadmeController should fail
        $I->runShellCommand("/app/yii_test readme/create --doi 888888 --outdir=/home/curators", false);
        $I->seeResultCodeIs(65);

        # Test getReadme function in ReadmeGenerator class to
        # throw exception when no dataset can be found for a DOI
        $expectedExceptionMessage = 'Dataset 888888 not found';
        $I->expectThrowable(new Exception($expectedExceptionMessage), function() {
            $readmeGenerator = new \app\components\ReadmeGenerator();
            $readmeGenerator->getReadme('888888');
        });
    }
}
