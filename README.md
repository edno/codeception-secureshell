# Codeception Secure Shell

[![Latest Version](https://img.shields.io/packagist/v/edno/codeception-secureshell.svg?style=flat-square)](https://packagist.org/packages/edno/codeception-secureshell)
[![Dependency Status](https://www.versioneye.com/user/projects/5763240c0735400035b94e9f/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/5763240c0735400035b94e9f)
[![Build Status](https://img.shields.io/travis/edno/codeception-secureshell.svg?style=flat-square)](https://travis-ci.org/edno/codeception-secureshell)
[![SensioLabs Insight](https://img.shields.io/sensiolabs/i/ff1e8b7c-36be-449f-9ce5-092968b2cda5.svg?style=flat-square)](https://insight.sensiolabs.com/projects/ff1e8b7c-36be-449f-9ce5-092968b2cda5)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/edno/codeception-secureshell.svg?style=flat-square)](https://scrutinizer-ci.com/g/edno/codeception-secureshell/?branch=master)
[![Coverage Status](https://img.shields.io/coveralls/edno/codeception-secureshell.svg?style=flat-square)](https://coveralls.io/github/edno/codeception-secureshell?branch=master)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](https://raw.githubusercontent.com/edno/codeception-secureshell/master/LICENSE)

The [Codeception](http://codeception.com/) module for **SSH commands** and **SSH tunnels**

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
