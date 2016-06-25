<?php


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
        $this->connection = $this->tester->openConnection('localhost', 32768, \Codeception\Extension\SecureShell::AUTH_PASSWORD, 'root', 'screencast');
        $this->tester->assertNotNull($this->connection, 'Not a valid connection or connection failed');
    }

    /**
     * @depends openConnection
     */
    public function closeConnection()
    {
        $this->tester->assertTrue($this->tester->closeConnection($this->connection));
    }

    /**
     * @depends openConnection
     * @depends closeConnection
     */
    public function openTwoConnections()
    {
        $connection1 = $this->tester->openConnection('localhost', 32768, \Codeception\Extension\SecureShell::AUTH_PASSWORD, 'root', 'screencast');
        $this->tester->assertNotNull($connection1, 'Not a valid connection or connection failed');
        $connection2 = $this->tester->openConnection('localhost', 32768, \Codeception\Extension\SecureShell::AUTH_PASSWORD, 'root', 'screencast');
        $this->tester->assertNotNull($connection2, 'Not a valid connection or connection failed');
        $this->tester->assertNotEquals($connection1, $connection2);
        $this->tester->closeConnection($connection1);
        $this->tester->closeConnection($connection2);
    }
}
