# Laravel Database Connection Checker

A simple Laravel Artisan command to check the database connection and diagnose common connection errors.

This tool helps developers quickly verify that their Laravel application can connect to the database and provides detailed diagnostics if the connection fails.

## Features

- Check a specific database connection.
- Check all configured database connections at once.
- Detailed and user-friendly error messages for common issues (e.g., authentication, connection refused, unknown database).
- Verbose mode to display connection parameters.
- Basic diagnostics, including listing table counts and checking read permissions.

## Installation

You can install the package via Composer:

```bash
composer require kozlovartem/laravel-db-check
```

The package will automatically register its service provider.

## Usage

### Check the Default Connection

To check the default database connection configured in your `.env` file, simply run:

```bash
php artisan db:check
```

### Check a Specific Connection

If you have multiple database connections, you can specify which one to check by passing the connection name as an argument:

```bash
php artisan db:check mysql_secondary
```

### Check All Connections

To check all connections configured in `config/database.php`, use the `--all` flag:

```bash
php artisan db:check --all
```

### Verbose Mode

For more detailed output, including the connection parameters being used, use the `--verbose` or `-v` flag. This is particularly useful for debugging.

```bash
php artisan db:check --verbose
```

### Sample Output

**Successful Connection:**

```
$ php artisan db:check
🔍 Laravel Database Connection Checker

Attempting to connect to 'mysql'...
✅ Successfully connected to database: my_database
📌 Server version: 8.0.27

🔧 Running diagnostics...
📊 Found 25 table(s) in the database
✍️  Testing write permissions...
✅ Read permissions: OK
```

**Failed Connection:**

```
$ php artisan db:check
🔍 Laravel Database Connection Checker

Attempting to connect to 'mysql'...
❌ PDO Connection Error:
Error Code: 1045
Error Message: SQLSTATE[HY000] [1045] Access denied for user 'forge'@'localhost' (using password: YES)

💡 Possible causes and solutions:

• Authentication Error: Check username and password
  - Verify credentials in .env file
  - Ensure database user exists and has proper privileges
  - Command: GRANT ALL PRIVILEGES ON my_database.* TO 'forge'@'localhost';

📝 Configuration file location: config/database.php
📝 Environment file location: .env
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
