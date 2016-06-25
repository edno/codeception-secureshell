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
        $connection = $this->tester->openConnection('localhost',
                                                        32768,
                                                        SecureShell::AUTH_PASSWORD,
                                                        'root',
                                                        'screencast');
        $this->tester->assertNotNull($connection, 'Not a valid connection or connection failed');
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
        $connection1 = $this->tester->openConnection('localhost',
                                                        32768,
                                                        SecureShell::AUTH_PASSWORD,
                                                        'root',
                                                        'screencast');
        $this->tester->assertNotNull($connection1, 'Not a valid connection or connection failed');
        $connection2 = $this->tester->openConnection('localhost',
                                                        32768,
                                                        SecureShell::AUTH_PASSWORD,
                                                        'root',
                                                        'screencast');
        $this->tester->assertNotNull($connection2, 'Not a valid connection or connection failed');
        $this->tester->assertNotEquals($connection1, $connection2);
        $this->tester->closeConnection($connection1);
        $this->tester->closeConnection($connection2);
    }

    /**
     * @depends openConnection
     */
    public function runRemoteCommand()
    {
        $connection = $this->tester->openConnection('localhost',
                                                        32768,
                                                        SecureShell::AUTH_PASSWORD,
                                                        'root',
                                                        'screencast');
        $res = $this->tester->runRemoteCommand($connection, 'echo "Test runRemoteCommand"');
        $this->tester->assertContains("Test runRemoteCommand", $res['STDOUT']);
    }

    /**
     * @depends runRemoteCommand
     */
    public function runRemoteCommandError()
    {
        $connection = $this->tester->openConnection('localhost',
                                                        32768,
                                                        SecureShell::AUTH_PASSWORD,
                                                        'root',
                                                        'screencast');
        $res = $this->tester->runRemoteCommand($connection, 'invalid_command');
        $this->tester->assertContains('invalid_command', $res['STDERR']);
    }
}
