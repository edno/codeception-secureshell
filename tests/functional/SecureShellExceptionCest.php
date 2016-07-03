<?php

use Codeception\Extension\SecureShell;
use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleConfigException;
use Codeception\Configuration;
use Codeception\Util\Stub;

class SecureShellExceptionCest
{
    /**
     * @env nochecking
     */
    public function openConnectionSSH2Error(FunctionalTester $I)
    {
        $I->expectException(ModuleException::class, function () use ($I) {
            $I->openConnection('thishostdoesnotexist');
        });
    }

    /**
     * @env nochecking
     */
    public function openConnectionAuthTypeError(FunctionalTester $I)
    {
        $I->expectException(ModuleException::class, function () use ($I) {
            $I->openConnection('localhost', 32768, 99);
        });
    }

    /**
     * @env nochecking
     */
    public function runCommandError(FunctionalTester $I)
    {
        $I->closeConnection();
        $I->expectException(ModuleException::class, function () use ($I) {
            $I->runRemoteCommand("echo 'Test runRemoteCommand'");
        });
    }

    /**
     * @env knownhosts
     */
    public function openConnectionInvalidFingerPrint(FunctionalTester $I)
    {
        // change config
        $reflectedClass = new \ReflectionClass('\Codeception\Extension\SecureShell');
        $reflectedProperty = $reflectedClass->getProperty('knownHostsFile');
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue('tests/_data/knownhosts.err');

        $I->expectException(ModuleException::class, function () use ($I) {
            $I->openConnection('localhost',
                                32768,
                                SecureShell::AUTH_PASSWORD,
                                'root',
                                'password');
        });
    }

    /**
     * @env knownhosts
     */
    public function openConnectionInvalidKnownHostFile(FunctionalTester $I)
    {
        // change config
        $reflectedClass = new \ReflectionClass('\Codeception\Extension\SecureShell');
        $reflectedProperty = $reflectedClass->getProperty('knownHostsFile');
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue('tests/_data/knownhosts');

        $I->expectException(ModuleException::class, function () use ($I) {
            $I->openConnection('localhost',
                                32768,
                                SecureShell::AUTH_PASSWORD,
                                'root',
                                'password');
        });
    }

    /**
     * @env nochecking
     */
    public function seeInRemoteOutputError(FunctionalTester $I)
    {
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_PASSWORD,
                            'root',
                            'password');
        $I->runRemoteCommand('echo "Test runRemoteCommand"');
        $I->expectException(\PHPUnit_Framework_ExpectationFailedException::class, function () use ($I) {
            $I->seeInRemoteOutput('Do not see Test runRemoteCommand');
        });
    }

    /**
     * @env nochecking
     */
    public function dontSeeInRemoteOutputError(FunctionalTester $I)
    {
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_PASSWORD,
                            'root',
                            'password');
        $I->runRemoteCommand('echo "Test runRemoteCommand"');
        $I->expectException(\PHPUnit_Framework_ExpectationFailedException::class, function () use ($I) {
            $I->dontSeeInRemoteOutput('Test runRemoteCommand');
        });
    }

    /**
     * @env nochecking
     */
    public function seeRemoteFileError(FunctionalTester $I)
    {
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_PASSWORD,
                            'root',
                            'password');
        $I->runRemoteCommand('echo "remoteFile" > remote.file');
        $I->expectException(\PHPUnit_Framework_ExpectationFailedException::class, function () use ($I) {
            $I->seeRemoteFile('remote.error');
        });
    }

    /**
     * @env nochecking
     */
    public function dontSeeRemoteFileError(FunctionalTester $I)
    {
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_PASSWORD,
                            'root',
                            'password');
        $I->expectException(\PHPUnit_Framework_ExpectationFailedException::class, function () use ($I) {
            $I->dontSeeRemoteFile('remote.file');
        });
    }

    /**
     * @env nochecking
     */
    public function grabRemoteFileError(FunctionalTester $I)
    {
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_PASSWORD,
                            'root',
                            'password');
        $I->expectException(ModuleException::class, function () use ($I) {
            $I->grabRemoteFile('/root/remote.error');
        });
    }

    /**
     * @env nochecking
     */
    public function grabRemoteDirError(FunctionalTester $I)
    {
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_PASSWORD,
                            'root',
                            'password');
        $I->expectException(ModuleException::class, function () use ($I) {
            $I->grabRemoteDir('/invalidfolder');
        });
    }

    /**
     * @env nochecking
     */
    public function seeRemoteDirError(FunctionalTester $I)
    {
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_PASSWORD,
                            'root',
                            'password');
        $I->expectException(\PHPUnit_Framework_ExpectationFailedException::class, function () use ($I) {
            $I->seeRemoteDir('/root/invalidfolder');
        });
    }

    /**
     * @env nochecking
     */
    public function dontSeeRemoteDirError(FunctionalTester $I)
    {
        $I->openConnection('localhost',
                            32768,
                            SecureShell::AUTH_PASSWORD,
                            'root',
                            'password');
        $I->runRemoteCommand('mkdir -p newfolder');
        $I->expectException(\PHPUnit_Framework_ExpectationFailedException::class, function () use ($I) {
            $I->dontSeeRemoteDir('/root/newfolder');
        });
    }
}
