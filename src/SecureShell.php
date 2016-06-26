<?php
namespace Codeception\Extension;

use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleConfigException;
use Codeception\Module;
use \SplFileObject;
use \RuntimeException;
use \Exception;

class SecureShell extends Module
{

    const DEFAULT_PORT  = 22;
    const AUTH_PASSWORD = 1;
    const AUTH_PUBKEY   = 2;
    const AUTH_HOSTKEY  = 3;
    const AUTH_AGENT    = 4;
    const AUTH_NONE     = 0;

    protected $config = [];

    protected $requiredFields = [];

    protected static $knownHostsFile = '~/.ssh/known_hosts'; // configuration

    protected static $acceptUnknownHost = true; // configuration

    protected $tunnels = [];

    protected $connection;

    private $output;

    public function openConnection($host,
                                    $port = SecureShell::DEFAULT_PORT,
                                    $auth = SecureShell::AUTH_PASSWORD,
                                    ...$args)
    {
        $uid = null;
        $callbacks = array('disconnect' => [$this, '_disconnect']);

        try {
            $connection = ssh2_connect($host, $port, $callbacks);
            if (!$connection) {
                throw new ModuleException(get_class($this), "Unable to connect to {$host} on port {$port}");
            } else {
                $fp = $this->__checkFingerprint($connection);

                if ($this->__authenticate($connection, $auth, ...$args) === false) {
                    throw new ModuleException(get_class($this), "Authentication failed on server {$host}:{$port}");
                } else {
                    $uid = hash('crc32', uniqid($fp), false);
                    $this->connection = $connection;
                }
            }
        } catch (ModuleException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ModuleException(get_class($this), $e->getMessage());
        }
        return $uid;
    }

    public function closeConnection() {
        $this->connection = null;
        return true;
    }

    public function getConnection() {
        return $this->connection;
    }

    protected function __authenticate($connection, $method, ...$args)
    {
        switch ($method) {
            case SecureShell::AUTH_PASSWORD:
                return ssh2_auth_password($connection, ...$args);
            case SecureShell::AUTH_PUBKEY:
                return ssh2_auth_pubkey_file($connection, ...$args);
            case SecureShell::AUTH_HOSTKEY:
                return ssh2_auth_hostbased_file($connection, ...$args);
            case SecureShell::AUTH_AGENT:
                return ssh2_auth_agent($connection, ...$args);
            case SecureShell::AUTH_NONE:
                return ssh2_auth_none($connection, ...$args);
            default:
                throw new ModuleException(get_class($this), 'Unsupported authentication method');
        }
    }

    protected function __checkFingerprint($connection)
    {
        $knownHost = false;
        try {
            $fingerprint = ssh2_fingerprint($connection, SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX);
            $file = new SplFileObject(static::$knownHostsFile);
            $file->setFlags(SplFileObject::READ_CSV);
            $file->setCsvControl(' ');
            foreach ($file as $entry) {
                list(,, $fp) = $entry;
                $knownHost = (strcmp($fp, $fingerprint) !== 0);
                if ($knownHost === true) {
                    break;
                }
            }
            $knownHost = $knownHost || static::$acceptUnknownHost;

            if ($knownHost === false) {
                throw new ModuleException(get_class($this), 'Unable to verify server identity!');
            }
        } catch (RuntimeException $e) {
            if (static::$acceptUnknownHost === false) {
                throw new ModuleException(get_class($this), 'Unable to verify server identity!');
            }
        }
        return $fingerprint;
    }

    protected function __disconnect()
    {
        $this->connection = null;
    }

    /** Remote Commands methods **/

    public function runRemoteCommand($command)
    {
        try {
            $stream = ssh2_exec($this->connection, $command);
            stream_set_blocking($stream, true);
            $errStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
            $this->output['STDOUT'] = stream_get_contents($stream);
            $this->output['STDERR'] = stream_get_contents($errStream);
            return $this->output;
        } catch (Exception $e) {
            throw new ModuleException(get_class($this), $e->getMessage());
        }
    }

    public function seeInRemoteOutput($text)
    {
        \PHPUnit_Framework_Assert::assertContains($text, $this->output['STDOUT']);
    }

    public function dontSeeInRemoteOutput($text)
    {
        \PHPUnit_Framework_Assert::assertNotContains($text, $this->output['STDOUT']);
    }

    /** Remote Files methods **/

    public function seeRemoteFile($filename)
    {
        $sftp = ssh2_sftp($this->connection);
        $res = ssh2_sftp_stat($sftp, $filename);
        \PHPUnit_Framework_Assert::assertNotEmpty($res);
    }

    public function dontSeeRemoteFile($filename)
    {
        $sftp = ssh2_sftp($this->connection);
        try {
            $res = (bool) ssh2_sftp_stat($sftp, $filename);
        } catch (Exception $e) {
            $res = false;
        }
        \PHPUnit_Framework_Assert::assertFalse($res);
    }

    public function grabRemoteFile($filename)
    {
        try {
            $sftp = ssh2_sftp($this->connection);
            return file_get_contents("ssh2.sftp://{$sftp}/{$filename}");
        } catch (Exception $e) {
            throw new ModuleException(get_class($this), $e->getMessage());
        }
    }

    /** Remote Dir methods **/

    public function seeRemoteDir($dirname)
    {
        try {
            $res = (bool) $this->grabRemoteDir($dirname);
        } catch (Exception $e) {
            $res = false;
        }
        \PHPUnit_Framework_Assert::assertTrue($res);
    }

    public function dontSeeRemoteDir($dirname)
    {
        try {
            $res = (bool) $this->grabRemoteDir($dirname);
        } catch (Exception $e) {
            $res = false;
        }
        \PHPUnit_Framework_Assert::assertFalse($res);
    }

    public function grabRemoteDir($dirname)
    {
        $res = null;
        try {
            $sftp = ssh2_sftp($this->connection);
            $res = scandir("ssh2.sftp://{$sftp}/{$dirname}");
        } catch (Exception $e) {
            throw new ModuleException(get_class($this), $e->getMessage());
        }
        return $res;
    }

    /** Tunnel methods **/

    public function openRemoteTunnel()
    {

    }

    public function closeRemoteTunnel()
    {

    }

}
