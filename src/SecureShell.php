<?php
namespace Codeception\Extension;

use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleConfigException;
use Codeception\Module;
use Codeception\Configuration;
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

    protected $config = ['StrictHostKeyChecking', 'KnownHostsFile'];

    protected $requiredFields = [];

    protected static $knownHostsFile = '';

    protected static $strictHostKeyChecking = false;

    protected $connection;

    private $output;

    /**
     * @codeCoverageIgnore
     */
    public function _initialize()
    {
        if (isset($this->config['StrictHostKeyChecking'])) {
            static::$strictHostKeyChecking = (bool) $this->config['StrictHostKeyChecking'];
        }
        if (isset($this->config['KnownHostsFile'])) {
            static::$knownHostsFile = $this->config['KnownHostsFile'];
        } elseif (static::$strictHostKeyChecking) {
            // default KnownHostsFile if StrictHostKeyChecking enabled
            static::$knownHostsFile = Configuration::projectDir().'known_hosts';
        }
        // check that a KnownHostsFile exists if StrictHostKeyChecking enabled
        if (static::$strictHostKeyChecking && !file_exists(static::$knownHostsFile)) {
            throw new ModuleConfigException(get_class($this), 'KnownHostsFile "'.static::$knownHostsFile.'" not found');
        }
    }

    public function openConnection($host,
                                    $port = SecureShell::DEFAULT_PORT,
                                    $auth = SecureShell::AUTH_PASSWORD,
                                    ...$args)
    {
        $callbacks = array('disconnect' => [$this, '_disconnect']);

        try {
            $connection = ssh2_connect($host, $port, $callbacks);
            if (!$connection) {
                throw new ModuleException(get_class($this), "Unable to connect to {$host} on port {$port}");
            } else {
                $this->__checkFingerprint($connection);

                if ($this->__authenticate($connection, $auth, ...$args) === false) {
                    throw new ModuleException(get_class($this), "Authentication failed on server {$host}:{$port}");
                } else {
                    $this->connection = $connection;
                }
            }
        } catch (ModuleException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ModuleException(get_class($this), $e->getMessage());
        }
        return $this->connection;
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
            $fingerprint = ssh2_fingerprint($connection, SSH2_FINGERPRINT_MD5);
            if (file_exists(static::$knownHostsFile)) {
                $file = new SplFileObject(static::$knownHostsFile);
                $file->setFlags(SplFileObject::READ_CSV);
                $file->setCsvControl(' ');
                foreach ($file as $entry) {
                    list(, $fp,,) = $entry;
                    $fp = preg_replace('/(?:MD5)?:/', '', $fp);
                    $knownHost = (strcasecmp($fp, $fingerprint) === 0);
                    if ($knownHost) {
                        break;
                    }
                }
            }
            $knownHost = $knownHost || !static::$strictHostKeyChecking;

            if ($knownHost === false) {
                throw new ModuleException(get_class($this), 'Unable to verify server identity!');
            }
        } catch (RuntimeException $e) {
            if (static::$strictHostKeyChecking) {
                throw new ModuleException(get_class($this), 'Unable to verify server identity!');
            }
        }
        return $fingerprint;
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

}
