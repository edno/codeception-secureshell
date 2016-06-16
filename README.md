# codeception-secureshell
The [Codeception](http://codeception.com/) module for **SSH commands and SSH tunnels**

## Minimum Requirements
- Codeception 2.2
- PHP 5.4

## Installation
The extension can be installed using [Composer](https://getcomposer.org)

```bash
$ composer require edno/codeception-secureshell
```

Be sure to enable the extension in `codeception.yml` as shown in
[configuration](#configuration) below.

## Configuration
Enabling **Gherkin Param** is done in `codeception.yml`.

```yaml
extensions:
    enabled:
        - Codeception\Extension\SecureShell
```
