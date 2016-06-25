<?php

use Codeception\Extension\SecureShell;
use Codeception\Exception\ModuleException;;

class SecureShellCest
{
    private $connection = null;
    private $tester;

    public function _before(FunctionalTester $I)
    {
        $this->tester = $I;
    }

    public function _after()
    {
    }

    // tests
    public function openConnection()
    {
        $this->connection = $this->tester->openConnection('localhost',
                                                        32768,
                                                        SecureShell::AUTH_PASSWORD,
                                                        'root',
                                                        'screencast');
        $this->tester->assertNotNull($this->connection, 'Not a valid connection or connection failed');
    }

    /**
     * @depends openConnection
     */
    public function closeConnection()
    {
        $connection = $this->tester->openConnection('localhost',
                                                        32768,
                                                        SecureShell::AUTH_PASSWORD,
                                                        'root',
                                                        'screencast');
        $this->tester->assertTrue($this->tester->closeConnection($connection));
    }

    /**
     * @depends openConnection
     * @depends closeConnection
     */
    public function openTwoConnections()
    {
        $connection2 = $this->tester->openConnection('localhost',
                                                        32768,
                                                        SecureShell::AUTH_PASSWORD,
                                                        'root',
                                                        'screencast');
        $this->tester->assertNotNull($connection2, 'Not a valid connection or connection failed');
        $this->tester->assertNotEquals($this->connection, $connection2);
        $this->tester->closeConnection($connection2);
    }

    /**
     * @depends openConnection
     */
    public function runRemoteCommand()
    {
        $res = $this->tester->runRemoteCommand($this->connection, "echo 'Test runRemoteCommand'");
        $this->tester->assertContains('Test runRemoteCommand', $res['STDOUT']);
    }

    /**
     * @depends runRemoteCommand
     */
    public function runRemoteCommandError()
    {
        $res = $this->tester->runRemoteCommand($this->connection, 'invalid_command');
        $this->tester->assertContains('invalid_command', $res['STDERR']);
    }

    /**
     * @depends runRemoteCommand
     */
    public function seeInRemoteOutput()
    {
        $this->tester->runRemoteCommand($this->connection, 'echo "Test runRemoteCommand"');
        $this->tester->seeInRemoteOutput('Test runRemoteCommand');
    }

    /**
     * @depends runRemoteCommand
     */
    public function dontSeeInRemoteOutput()
    {
        $this->tester->runRemoteCommand($this->connection, 'echo "Test runRemoteCommand"');
        $this->tester->dontSeeInRemoteOutput('Dont see Test runRemoteCommand');
    }

    /**
     * @depends runRemoteCommand
     */
    public function seeRemoteFile()
    {
        $this->tester->runRemoteCommand($this->connection, 'echo "remoteFile" > remote.file');
        $this->tester->seeRemoteFile($this->connection, 'remote.file');
    }

    /**
     * @depends openConnection
     */
    public function dontSeeRemoteFile()
    {
        $this->tester->dontSeeRemoteFile($this->connection, 'remote.nofile');
    }

    /**
     * @depends seeRemoteFile
     */
    public function grabRemoteFile()
    {
        $res = $this->tester->grabRemoteFile($this->connection, '/root/remote.file');
        $this->tester->assertContains('remoteFile', $res);
    }
}
