<?php

use Codeception\Extension\SecureShell;
use Codeception\Exception\ModuleException;;

class SecureShellCest
{
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
        $this->tester->openConnection('localhost',
                                        32768,
                                        SecureShell::AUTH_PASSWORD,
                                        'root',
                                        'password');
        $this->tester->assertNotNull('Not a valid connection or connection failed');
    }

    /**
     * @depends openConnection
     */
    public function closeConnection()
    {
        $this->tester->openConnection('localhost',
                                        32768,
                                        SecureShell::AUTH_PASSWORD,
                                        'root',
                                        'password');
        $this->tester->assertTrue($this->tester->closeConnection());
    }

    /**
     * @depends openConnection
     */
    public function runRemoteCommand()
    {
        $this->tester->openConnection('localhost',
                                        32768,
                                        SecureShell::AUTH_PASSWORD,
                                        'root',
                                        'password');
        $res = $this->tester->runRemoteCommand("echo 'Test runRemoteCommand'");
        $this->tester->assertContains('Test runRemoteCommand', $res['STDOUT']);
    }

    /**
     * @depends runRemoteCommand
     */
    public function runRemoteCommandError()
    {
        $this->tester->openConnection('localhost',
                                        32768,
                                        SecureShell::AUTH_PASSWORD,
                                        'root',
                                        'password');
        $res = $this->tester->runRemoteCommand('invalid_command');
        $this->tester->assertContains('invalid_command', $res['STDERR']);
    }

    /**
     * @depends runRemoteCommand
     */
    public function seeInRemoteOutput()
    {
        $this->tester->openConnection('localhost',
                                        32768,
                                        SecureShell::AUTH_PASSWORD,
                                        'root',
                                        'password');
        $this->tester->runRemoteCommand('echo "Test runRemoteCommand"');
        $this->tester->seeInRemoteOutput('Test runRemoteCommand');
    }

    /**
     * @depends runRemoteCommand
     */
    public function dontSeeInRemoteOutput()
    {
        $this->tester->openConnection('localhost',
                                        32768,
                                        SecureShell::AUTH_PASSWORD,
                                        'root',
                                        'password');
        $this->tester->runRemoteCommand('echo "Test runRemoteCommand"');
        $this->tester->dontSeeInRemoteOutput('Dont see Test runRemoteCommand');
    }

    /**
     * @depends runRemoteCommand
     */
    public function seeRemoteFile()
    {
        $this->tester->openConnection('localhost',
                                        32768,
                                        SecureShell::AUTH_PASSWORD,
                                        'root',
                                        'password');
        $this->tester->runRemoteCommand('echo "remoteFile" > remote.file');
        $this->tester->seeRemoteFile('remote.file');
    }

    /**
     * @depends openConnection
     */
    public function dontSeeRemoteFile()
    {
        $this->tester->openConnection('localhost',
                                        32768,
                                        SecureShell::AUTH_PASSWORD,
                                        'root',
                                        'password');
        $this->tester->dontSeeRemoteFile('remote.nofile');
    }

    /**
     * @depends seeRemoteFile
     */
    public function grabRemoteFile()
    {
        $this->tester->openConnection('localhost',
                                        32768,
                                        SecureShell::AUTH_PASSWORD,
                                        'root',
                                        'password');
        $res = $this->tester->grabRemoteFile('/root/remote.file');
        $this->tester->assertContains('remoteFile', $res);
    }

    /**
     * @depends openConnection
     * @depends seeRemoteFile
     */
    public function grabRemoteDir()
    {
        $this->tester->openConnection('localhost',
                                        32768,
                                        SecureShell::AUTH_PASSWORD,
                                        'root',
                                        'password');
        $res = $this->tester->grabRemoteDir('/root/.');
        $this->tester->assertContains('remote.file', $res);
    }

    /**
     * @depends grabRemoteDir
     */
    public function seeRemoteDir()
    {
        $this->tester->openConnection('localhost',
                                        32768,
                                        SecureShell::AUTH_PASSWORD,
                                        'root',
                                        'password');
        $this->tester->runRemoteCommand('mkdir -p testdir');
        $this->tester->seeRemoteDir('/root/testdir');
    }

    /**
     * @depends grabRemoteDir
     */
    public function dontSeeRemoteDir()
    {
        $this->tester->openConnection('localhost',
                                        32768,
                                        SecureShell::AUTH_PASSWORD,
                                        'root',
                                        'password');
        $this->tester->dontSeeRemoteDir('/root/dirnotexist');
    }
}
