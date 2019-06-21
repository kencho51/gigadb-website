<?php

namespace backend\tests;

use backend\models\FiledropAccount;
use backend\models\DockerManager;

use \Docker\API\Model\{
        IdResponse,
        ContainersIdExecPostBody,
        ExecIdStartPostBody,
        ContainerSummaryItem,
    } ;

use \Docker\Docker ;

class FiledropAccountTest extends \Codeception\Test\Unit
{
    /**
     * @var \backend\tests\UnitTester
     */
    protected $tester;

    /**
     * @var \backend\models\FiledropAccount
     */
    protected $filedrop;

    protected function _before()
    {
        $this->cleanUpDirectories();
        $this->filedrop = new FiledropAccount();
    }

    protected function _after()
    {
        $this->cleanUpDirectories();
    }

    private function cleanUpDirectories()
    {
        if ( file_exists("/var/incoming/ftp/100001") ) {
            exec("rm -rf /var/incoming/ftp/100001");
        }

        if ( file_exists("/var/repo/100001") ) {
            exec ("rm -rf /var/repo/100001");
        }

        if ( file_exists("/var/private/100001") ) {
            exec("rm -rf /var/private/100001");
        }

    }
    /**
     * test FileDrop can create directory for file upload pipeline
     */
    public function testCanCreateWritableDirectories()
    {



        $this->assertFalse(file_exists("/var/incoming/ftp/100001"));
        $this->assertFalse(file_exists("/var/repo/100001"));
        $this->assertFalse(file_exists("/var/private/100001"));

        $result = $this->filedrop->createDirectories("100001");

        $this->assertTrue(file_exists("/var/incoming/ftp/100001"));
        $this->assertTrue(file_exists("/var/repo/100001"));
        $this->assertTrue(file_exists("/var/private/100001"));

        $this->assertEquals("0770", substr(sprintf('%o', fileperms('/var/incoming/ftp/100001')), -4) );
        $this->assertEquals("0755", substr(sprintf('%o', fileperms('/var/repo/100001')), -4) );
        $this->assertEquals("0750", substr(sprintf('%o', fileperms('/var/private/100001')), -4) );

        $this->assertTrue($result);

    }

    /**
     * test FileDrop can create create a token file
     */
    public function testCanCreateTokens()
    {
        $this->assertFalse(file_exists("/var/private/100001/token_file"));
        mkdir("/var/private/100001");
        chmod("/var/private/100001", 0770);

        $result1 = $this->filedrop->makeToken('100001','token_file');
        $this->assertTrue(file_exists("/var/private/100001/token_file"));
        $token1 = file("/var/private/100001/token_file");
        $this->assertEquals($token1[0],$token1[1]);

        $result2 = $this->filedrop->makeToken('100001','token_file');
        $this->assertTrue(file_exists("/var/private/100001/token_file"));
        $token2 = file("/var/private/100001/token_file");
        $this->assertEquals($token2[0],$token2[1]);

        $this->assertTrue($result1);
        $this->assertTrue($result2);
        $this->assertNotEquals($token1[0], $token2[0]);

    }

    /**
     * test sending  upload account creation to the ftpd container
     * This test is to specify the internal logic (behaviours), not end-to-end
     * end-to-end testing of the docker interaction will be done in functional tests
     */
    public function testCreateFTPAccount()
    {
        $uploaderCommandArray = ["bash","-c","/usr/bin/pure-pw useradd uploader-100001 -f /etc/pure-ftpd/passwd/pureftpd.passwd -m -u uploader -d /home/uploader/100001  < /var/private/100001/uploader_token.txt"] ;

        $downloaderCommandArray = ["bash","-c","/usr/bin/pure-pw useradd downloader-100001 -f /etc/pure-ftpd/passwd/pureftpd.passwd -m -u downloader -d /home/downloader/100001  < /var/private/100001/downloader_token.txt"] ;

         $doi = "100001";

        $mockDockerManager = $this->getMockBuilder(\backend\models\DockerManager::class)
                    ->setMethods(['loadAndRunCommand'])
                    ->disableOriginalConstructor()
                    ->getMock();

        $mockDockerManager->expects($this->at(0))
                ->method('loadAndRunCommand')
                ->with(
                    $this->equalTo("ftpd"),
                    $this->equalTo($uploaderCommandArray)
                );

        $mockDockerManager->expects($this->at(1))
                ->method('loadAndRunCommand')
                ->with(
                    $this->equalTo("ftpd"),
                    $this->equalTo($downloaderCommandArray)
                );

        $response = $this->filedrop->createFTPAccount( $mockDockerManager, $doi );
    }

}