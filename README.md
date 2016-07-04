# Codeception Secure Shell

[![Latest Version](https://img.shields.io/packagist/v/edno/codeception-secureshell.svg?style=flat-square)](https://packagist.org/packages/edno/codeception-secureshell)
[![Dependency Status](https://www.versioneye.com/user/projects/5763240c0735400035b94e9f/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/5763240c0735400035b94e9f)
[![Build Status](https://img.shields.io/travis/edno/codeception-secureshell.svg?style=flat-square)](https://travis-ci.org/edno/codeception-secureshell)
[![SensioLabs Insight](https://img.shields.io/sensiolabs/i/ff1e8b7c-36be-449f-9ce5-092968b2cda5.svg?style=flat-square)](https://insight.sensiolabs.com/projects/ff1e8b7c-36be-449f-9ce5-092968b2cda5)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/edno/codeception-secureshell.svg?style=flat-square)](https://scrutinizer-ci.com/g/edno/codeception-secureshell/?branch=master)
[![Coverage Status](https://img.shields.io/coveralls/edno/codeception-secureshell.svg?style=flat-square)](https://coveralls.io/github/edno/codeception-secureshell?branch=master)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](https://raw.githubusercontent.com/edno/codeception-secureshell/master/LICENSE)

The [Codeception](http://codeception.com/) module for **SSH commands**, **SFTP access** and **SSH tunnels**

If you just need a **SFTP** connection, please consider the in-built Codeception [FTP module](http://codeception.com/docs/modules/FTP).

## Roadmap
- [x] **0.1**: Basic commands for testing remote file system and commands
- [ ] 0.2: Tunnel commands
- [ ] 0.3: Services and advanced commands

## Minimum Requirements
- Codeception 2.2
- PHP 5.6
- Extension [SecureShell2](http://www.php.net/ssh2)

## Installation
The module can be installed using [Composer](https://getcomposer.org)

```bash
$ composer require edno/codeception-secureshell
```

Be sure to enable the module as shown in
[configuration](#configuration) below.

## Configuration
Enabling **Secure Shell** is done in your configuration file `.yml`.

```yaml
module:
    enabled:
        - Codeception\Extension\SecureShell
```

### Parameters
By default the module always [accepts the host public key fingerprint](https://en.wikibooks.org/wiki/Guide_to_Unix/Explanations/Connecting_to_Remote_Unix#Accepting_the_Key_Fingerprint).  

You can enable a strict mode where only known hosts will be accepted by setting the configuration parameter `StrictHostKeyChecking` to `true`.  
Once enabled, the module will verify the host fingerprint against a `known_hosts` file located at the root directory of your Codeception project.
```yaml
modules:
    config:
        Codeception\Extension\SecureShell:
            StrictHostKeyChecking: true
```
If you want to reuse an existing `known_hosts` file, you can use the parameter `KnownHostsFile` for specifying the location of the file.
```yaml
modules:
    config:
        Codeception\Extension\SecureShell:
            StrictHostKeyChecking: true
            KnownHostsFile: '/etc/ssh/known_hosts'
```
The file must respect the [OpenSSH **~/.ssh/known_hosts** format](https://en.wikibooks.org/wiki/OpenSSH/Client_Configuration_Files#.7E.2F.ssh.2Fknown_hosts).

## Documentation
Documentation is available in the wiki [List of commands and methods](https://github.com/edno/codeception-secureshell/wiki/List-of-commands-and-methods).
