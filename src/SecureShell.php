<?php
namespace Codeception\Extension;

use Codeception\Exception\ModuleException;
use \SplFileObject;
use \RuntimeException;

class SecureShell extends \Codeception\Platform\Extension
{

    const DEFAULT_PORT  = 22;
    const AUTH_PASSWORD = 1;
    const AUTH_PUBKEY   = 2;
    const AUTH_HOSTKEY  = 3;
    const AUTH_AGENT    = 4;
    const AUTH_NONE     = 0;

    // list events to listen to
    public static $events = [];

    protected static $knownHostsFile = '~/.ssh/known_hosts';

    protected static $tunnels = [];

    protected static $connections = [];

    public function openConnection($host,
                                   $port = SecureShell::DEFAULT_PORT,
                                   $auth = SecureShell::AUTH_PASSWORD,
                                   ...$args)
    {
        $uid = null;
        $callbacks = array('disconnect' => [$this, '_disconnect']);

        if (!($connection = ssh2_connect($host, $port, $callbacks))) {
            throw new ModuleException("Cannot connect to server {$host}:{$port}");
        } else {
            $this->_checkFingerprint($connection);

            if ($this->_authenticate($connection, ...$args) === false) {
                throw new ModuleException("Authentication failed on server {$host}:{$port}");
            } else {
                $uid = uniqid('ssh_');
                $this->connections[$uid] = ['host' => $host,
                                            'port' => $port,
                                            'fingerprint' => $fp,
                                            'auth_method' => $auth,
                                            'resource' => $connection];
            }
        }
        return $uid;
    }

    public function closeConnection($uid) {
        switch ($this->_isValidConnnection($uid)) {
            case 0:
            case 1:
                unset($this->connections[$uid]);
                break;
            default:
                throw new ModuleException("{$uid} is not a valid SSH connection");
        }
    }

    protected function _isValidConnnection($uid) {
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

    protected function _authenticate($connection, $method, ...$args)
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
                throw new ModuleException('Unsupported authentication method');
        }
    }

    protected function _checkFingerprint($connection, $acceptUnknown = false)
    {
        $fingerprint = ssh2_fingerprint($connection, SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX);
        $knownHost = false;
        try {
            $file = new SplFileObject($this->knownHostsFile);
            $file->setFlags(SplFileObject::READ_CSV);
            $file->setCsvControl(' ');
            foreach ($file as $entry) {
                list($host, $method, $fp) = $entry;
                $knownHost = (strcmp($fp, $fingerprint) !== 0);
                if ($knownHost === true) {
                    break;
                }
            }
            $knownHost = $knownHost || $acceptUnknown;

            if ($knownHost === false) {
                throw new ModuleException('Unable to verify server identity!');
            }
        } catch (RuntimeException $e) {
            if ($acceptUnknown === false) {
                throw new ModuleException('Unable to verify server identity!');
            }
        }
        return true;
    }

    protected function _disconnect()
    {
        foreach ($this->connections as $id => $connection){
            if (is_resource($connection['resource']) !== true) {
                unset($this->connections[$id]);
            }
        }
    }

    /** Remote Commands methods **/

    public function runRemoteCommand()
    {

    }

    /** Remote Files methods **/

    public function seeRemoteFile()
    {

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
