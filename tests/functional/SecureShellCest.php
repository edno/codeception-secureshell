<?php


class SecureShellCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    // tests
    public function openConnectionTest(FunctionalTester $I)
    {
        $connection = $I->openConnection('localhost', 32768, \Codeception\Extension\SecureShell::AUTH_PASSWORD, 'root', 'screencast');
        $this->assertNotNull($connection, 'Not a valid connection or connection failed');
    }
}
