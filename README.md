# Solo Settings

[![Latest Version on Packagist](https://img.shields.io/packagist/v/solophp/settings.svg?style=flat-square)](https://packagist.org/packages/solophp/settings)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/solophp/settings.svg?style=flat-square)](https://packagist.org/packages/solophp/settings)

A simple and efficient PHP settings management package that provides an interface to store and retrieve application settings from a database.

## Features

- Easy integration with existing database connections
- Automatic serialization and deserialization of complex data types
- Support for both object-oriented and array-like access to settings
- Efficient caching of settings to minimize database queries

## Installation

```bash
composer require solophp/settings
```

## Requirements

- PHP 8.1+
- PDO extension

### Basic Setup

First, ensure that you have a table for storing settings. The table should have two columns: `name` (string) and `value` (string).

Example table schema:

```sql
CREATE TABLE `settings` (
  `name` VARCHAR(255) NOT NULL,
  `value` TEXT NOT NULL,
  PRIMARY KEY (`name`)
);
```

## Usage

First, create an instance of the `Settings` class:

```php
use Solo\Settings\Settings;

$pdo = new PDO('mysql:host=localhost;dbname=app', 'user', 'pass');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$settings = new Settings($pdo, 'settings');
```

### Retrieving Settings

You can retrieve settings using either method calls or property access:

```php
$value = $settings->get('my_setting');
// or
$value = $settings->my_setting;
```

### Storing Settings

Similarly, you can store settings using either method calls or property access:

```php
$settings->set('my_setting', 'new_value');
// or
$settings->my_setting = 'new_value';
```

### Get all Settings

Get all settings as an associative array.:

```php
$settings->getAll();
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.