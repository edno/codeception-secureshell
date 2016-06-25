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

    protected $connections = [];

    private $output;

    public function openConnection( $host,
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
                    $this->connections = array_merge($this->connections,
                                        [$uid => ['host' => $host,
                                                'port' => $port,
                                                'fingerprint' => $fp,
                                                'auth_method' => $auth,
                                                'resource' => $connection]
                                        ]);
                }
            }
        } catch (ModuleException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ModuleException(get_class($this), $e->getMessage());
        }
        return $uid;
    }

    public function closeConnection($uid) {
        switch ($this->__isValidConnnection($uid)) {
            case 0:
            case 1:
                unset($this->connections[$uid]);
                break;
            default:
                throw new ModuleException(get_class($this), "{$uid} is not a valid SSH connection");
        }
        return true;
    }

    public function getConnection($uid) {
        return $this->connections[$uid]['resource'];
    }

    protected function __isValidConnnection($uid) {
        if (isset($this->connections[$uid])) {
            if (is_resource($this->connections[$uid]['resource'])) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return -1;
        }
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
                list($host, $method, $fp) = $entry;
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
        foreach ($this->connections as $id => $connection) {
            if (is_resource($connection['resource']) !== true) {
                unset($this->connections[$id]);
            }
        }
    }

    /** Remote Commands methods **/

    public function runRemoteCommand($session, $command)
    {
        $connection = $this->getConnection($session);
        $stream = ssh2_exec($connection, $command);
        stream_set_blocking($stream, true);
        $errStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
        $this->output['STDOUT'] = stream_get_contents($stream);
        $this->output['STDERR'] = stream_get_contents($errStream);
        return $this->output;
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

    public function seeRemoteFile($session, $filename)
    {
        $connection = $this->getConnection($session);
        $sftp = ssh2_sftp($connection);
        $res = ssh2_sftp_stat($sftp, $filename);
        \PHPUnit_Framework_Assert::assertNotEmpty($res);
    }

    public function dontSeeRemoteFile($session, $filename)
    {
        $connection = $this->getConnection($session);
        $sftp = ssh2_sftp($connection);
        try {
            $res = ssh2_sftp_stat($sftp, $filename);
        } catch(Exception $e) {
            $res = false;
        }
        \PHPUnit_Framework_Assert::assertFalse($res);
    }

    public function grabRemoteFile()
    {

    }

    public function copyRemoteFile()
    {

    }

    public function deleteRemoteFile()
    {

    }

    /** Remote Dir methods **/

    public function seeRemoteDir()
    {

    }

    public function dontSeeRemoteDir()
    {

    }

    public function copyRemoteDir()
    {

    }

    public function deleteRemoteDir()
    {

    }

    public function readRemoteDir()
    {

    }

    /** Tunnel methods **/

    public function openRemoteTunnel()
    {

    }

    public function closeRemoteTunnel()
    {

    }

}
