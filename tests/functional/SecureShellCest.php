<?php

use Codeception\Extension\SecureShell;
use Codeception\Configuration;

class SecureShellCest
{
    // tests
    public function openConnectionPassword(FunctionalTester $I)
    {
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_PASSWORD,
                            'root',
                            'password');
        $I->assertNotNull('Not a valid connection or connection failed');
    }

    public function openConnectionPublicKey(FunctionalTester $I)
    {
        $keys = Configuration::dataDir().'docker/keys/';
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_PUBKEY,
                            'user001',
                            $keys.'user001.pub',
                            $keys.'user001');
        $I->assertNotNull('Not a valid connection or connection failed');
    }

    /**
     * @skip
     * skipped no valid config for tests
     */
    public function openConnectionHostKey(FunctionalTester $I)
    {
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_HOSTKEY,
                            'root',
                            'localhost',
                            '/etc/ssh/ssh_host_rsa_key.pub',
                            '/etc/ssh/ssh_host_rsa_key',
                            '',
                            'root');
        $I->assertNotNull('Not a valid connection or connection failed');
    }

    public function openConnectionAgent(FunctionalTester $I)
    {
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_AGENT,
                            'user001');
        $I->assertNotNull('Not a valid connection or connection failed');
    }

    public function openConnectionNone(FunctionalTester $I)
    {
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_NONE,
                            'user002');
        $I->assertNotNull('Not a valid connection or connection failed');
    }

    /**
     * @depends openConnectionPassword
     */
    public function getConnection(FunctionalTester $I)
    {
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_PASSWORD,
                            'root',
                            'password');
        $I->assertNotNull($I->getConnection());
    }

    /**
     * @env nochecking
     * @depends openConnectionPassword
     */
    public function closeConnection(FunctionalTester $I)
    {
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_PASSWORD,
                            'root',
                            'password');
        $I->assertTrue($I->closeConnection());
    }

    /**
     * @env nochecking
     * @depends openConnectionPassword
     */
    public function runRemoteCommand(FunctionalTester $I)
    {
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_PASSWORD,
                            'root',
                            'password');
        $res = $I->runRemoteCommand("echo 'Test runRemoteCommand'");
        $I->assertContains('Test runRemoteCommand', $res['STDOUT']);
    }

    /**
     * @env nochecking
     * @depends runRemoteCommand
     */
    public function runRemoteCommandStdErr(FunctionalTester $I)
    {
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_PASSWORD,
                            'root',
                            'password');
        $res = $I->runRemoteCommand('invalid_command');
        $I->assertContains('invalid_command', $res['STDERR']);
    }

    /**
     * @env nochecking
     * @depends runRemoteCommand
     */
    public function seeInRemoteOutput(FunctionalTester $I)
    {
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_PASSWORD,
                            'root',
                            'password');
        $I->runRemoteCommand('echo "Test runRemoteCommand"');
        $I->seeInRemoteOutput('Test runRemoteCommand');
    }

    /**
     * @env nochecking
     * @depends runRemoteCommand
     */
    public function dontSeeInRemoteOutput(FunctionalTester $I)
    {
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_PASSWORD,
                            'root',
                            'password');
        $I->runRemoteCommand('echo "Test runRemoteCommand"');
        $I->dontSeeInRemoteOutput('Dont see Test runRemoteCommand');
    }

    /**
     * @env nochecking
     * @depends runRemoteCommand
     */
    public function seeRemoteFile(FunctionalTester $I)
    {
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_PASSWORD,
                            'root',
                            'password');
        $I->runRemoteCommand('echo "remoteFile" > remote.file');
        $I->seeRemoteFile('remote.file');
    }

    /**
     * @env nochecking
     * @depends openConnectionPassword
     */
    public function dontSeeRemoteFile(FunctionalTester $I)
    {
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_PASSWORD,
                            'root',
                            'password');
        $I->dontSeeRemoteFile('remote.nofile');
    }

    /**
     * @env nochecking
     * @depends seeRemoteFile
     */
    public function grabRemoteFile(FunctionalTester $I)
    {
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_PASSWORD,
                            'root',
                            'password');
        $res = $I->grabRemoteFile('/root/remote.file');
        $I->assertContains('remoteFile', $res);
    }

    /**
     * @env nochecking
     * @depends openConnectionPassword
     * @depends seeRemoteFile
     */
    public function grabRemoteDir(FunctionalTester $I)
    {
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_PASSWORD,
                            'root',
                            'password');
        $res = $I->grabRemoteDir('/root/.');
        $I->assertContains('remote.file', $res);
    }

    /**
     * @env nochecking
     * @depends grabRemoteDir
     * @depends runRemoteCommand
     */
    public function seeRemoteDir(FunctionalTester $I)
    {
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_PASSWORD,
                            'root',
                            'password');
        $I->runRemoteCommand('mkdir -p testdir');
        $I->seeRemoteDir('/root/testdir');
    }

    /**
     * @env nochecking
     * @depends grabRemoteDir
     */
    public function dontSeeRemoteDir(FunctionalTester $I)
    {
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_PASSWORD,
                            'root',
                            'password');
        $I->dontSeeRemoteDir('/root/dirnotexist');
    }
}
