<?php

use Codeception\Extension\SecureShell;
use Codeception\Exception\ModuleException;
use Codeception\Configuration;

class SecureShellCest
{
    private $tester;
    private $data;

    public function _before(FunctionalTester $I)
    {
        $this->tester = $I;
        $this->keys = Configuration::dataDir().'docker/keys/';
    }

    // tests
    public function openConnectionPassword()
    {
        $this->tester->openConnection('localhost',
                                        32768,
                                        SecureShell::AUTH_PASSWORD,
                                        'root',
                                        'password');
        $this->tester->assertNotNull('Not a valid connection or connection failed');
    }

    public function openConnectionPublicKey()
    {
        $this->tester->openConnection('localhost',
                                        32768,
                                        SecureShell::AUTH_PUBKEY,
                                        'user001',
                                        $this->keys.'user001.pub',
                                        $this->keys.'user001');
        $this->tester->assertNotNull('Not a valid connection or connection failed');
    }

    /**
     * @skip
     * skipped no valid config for tests
     */
    public function openConnectionHostKey()
    {
        $this->tester->openConnection('localhost',
                                        32768,
                                        SecureShell::AUTH_HOSTKEY,
                                        'root',
                                        'localhost',
                                        '/etc/ssh/ssh_host_rsa_key.pub',
                                        '/etc/ssh/ssh_host_rsa_key',
                                        '',
                                        'root');
        $this->tester->assertNotNull('Not a valid connection or connection failed');
    }

    public function openConnectionAgent()
    {
        $this->tester->openConnection('localhost',
                                        32768,
                                        SecureShell::AUTH_AGENT,
                                        'user001');
        $this->tester->assertNotNull('Not a valid connection or connection failed');
    }

    public function openConnectionNone()
    {
        $this->tester->openConnection('localhost',
                                        32768,
                                        SecureShell::AUTH_NONE,
                                        'user002');
        $this->tester->assertNotNull('Not a valid connection or connection failed');
    }

    /**
     * @depends openConnectionPassword
     */
    public function getConnection()
    {
        $this->tester->openConnection('localhost',
                                        32768,
                                        SecureShell::AUTH_PASSWORD,
                                        'root',
                                        'password');
        $this->tester->assertNotNull($this->tester->getConnection());
    }

    /**
     * @depends openConnectionPassword
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
     * @depends openConnectionPassword
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
     * @depends openConnectionPassword
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
     * @depends openConnectionPassword
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
     * @depends runRemoteCommand
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
