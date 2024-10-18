# Solo Settings

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

- PHP 7.4 or higher
- solophp/database

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
use Solo\Settings;
use Solo\Database;

$database = new Database(/* your database connection parameters */);
$settings = new Settings($database, 'settings_table');
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

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.