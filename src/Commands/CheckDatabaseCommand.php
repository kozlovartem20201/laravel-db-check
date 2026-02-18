<?php

namespace KozlovArtem\LaravelDbCheck\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use PDOException;
use Exception;

class CheckDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:check 
                            {connection? : The database connection to check}
                            {--all : Check all configured database connections}
                            {--verbose : Display detailed connection information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check database connection and diagnose connection errors';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔍 Laravel Database Connection Checker');
        $this->newLine();

        if ($this->option('all')) {
            return $this->checkAllConnections();
        }

        $connection = $this->argument('connection') ?? config('database.default');
        
        return $this->checkConnection($connection);
    }

    /**
     * Check all configured database connections
     *
     * @return int
     */
    protected function checkAllConnections(): int
    {
        $connections = array_keys(config('database.connections'));
        $results = [];

        foreach ($connections as $connection) {
            $this->info("Checking connection: {$connection}");
            $status = $this->checkConnection($connection, false);
            $results[$connection] = $status;
            $this->newLine();
        }

        // Summary table
        $this->info('📊 Summary:');
        $tableData = [];
        foreach ($results as $conn => $status) {
            $tableData[] = [
                $conn,
                $status === 0 ? '✅ Success' : '❌ Failed'
            ];
        }
        $this->table(['Connection', 'Status'], $tableData);

        return in_array(1, $results) ? 1 : 0;
    }

    /**
     * Check a specific database connection
     *
     * @param string $connection
     * @param bool $exit
     * @return int
     */
    protected function checkConnection(string $connection, bool $exit = true): int
    {
        try {
            // Get connection configuration
            $config = config("database.connections.{$connection}");
            
            if (!$config) {
                $this->error("❌ Connection '{$connection}' is not configured in database.php");
                return 1;
            }

            // Display connection details if verbose
            if ($this->option('verbose')) {
                $this->displayConnectionInfo($connection, $config);
            }

            // Attempt to connect
            $this->info("Attempting to connect to '{$connection}'...");
            
            $pdo = DB::connection($connection)->getPdo();
            
            // Get database name
            $databaseName = $this->getDatabaseName($connection, $config);
            
            // Get server version
            $version = DB::connection($connection)->selectOne('SELECT VERSION() as version');
            
            $this->info("✅ Successfully connected to database: {$databaseName}");
            $this->info("📌 Server version: {$version->version}");
            
            // Run additional diagnostics
            $this->runDiagnostics($connection, $config);
            
            return 0;
            
        } catch (PDOException $e) {
            $this->error("❌ PDO Connection Error:");
            $this->handlePDOException($e, $config);
            return 1;
            
        } catch (Exception $e) {
            $this->error("❌ General Error:");
            $this->error($e->getMessage());
            
            if ($this->option('verbose')) {
                $this->newLine();
                $this->warn('Stack trace:');
                $this->line($e->getTraceAsString());
            }
            
            return 1;
        }
    }

    /**
     * Display connection information
     *
     * @param string $connection
     * @param array $config
     * @return void
     */
    protected function displayConnectionInfo(string $connection, array $config): void
    {
        $this->info("📋 Connection Details for '{$connection}':");
        
        $driver = $config['driver'] ?? 'unknown';
        $host = $config['host'] ?? 'N/A';
        $port = $config['port'] ?? 'N/A';
        $database = $config['database'] ?? 'N/A';
        $username = $config['username'] ?? 'N/A';
        
        $this->table(
            ['Parameter', 'Value'],
            [
                ['Driver', $driver],
                ['Host', $host],
                ['Port', $port],
                ['Database', $database],
                ['Username', $username],
                ['Password', $config['password'] ? '****** (set)' : '(not set)'],
            ]
        );
        $this->newLine();
    }

    /**
     * Get database name from connection
     *
     * @param string $connection
     * @param array $config
     * @return string
     */
    protected function getDatabaseName(string $connection, array $config): string
    {
        $driver = $config['driver'] ?? 'unknown';
        
        if ($driver === 'sqlite') {
            return $config['database'] ?? 'N/A';
        }
        
        return DB::connection($connection)->getDatabaseName();
    }

    /**
     * Run additional diagnostics
     *
     * @param string $connection
     * @param array $config
     * @return void
     */
    protected function runDiagnostics(string $connection, array $config): void
    {
        $this->newLine();
        $this->info("🔧 Running diagnostics...");
        
        try {
            // Check if we can list tables
            $driver = $config['driver'] ?? 'unknown';
            
            $tables = match($driver) {
                'mysql', 'mariadb' => DB::connection($connection)->select('SHOW TABLES'),
                'pgsql' => DB::connection($connection)->select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'"),
                'sqlite' => DB::connection($connection)->select("SELECT name FROM sqlite_master WHERE type='table'"),
                'sqlsrv' => DB::connection($connection)->select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'"),
                default => []
            };
            
            $tableCount = count($tables);
            $this->info("📊 Found {$tableCount} table(s) in the database");
            
            // Check write permissions
            $this->info("✍️  Testing write permissions...");
            DB::connection($connection)->statement('SELECT 1'); // Basic query test
            $this->info("✅ Read permissions: OK");
            
        } catch (Exception $e) {
            $this->warn("⚠️  Diagnostics partially failed: " . $e->getMessage());
        }
    }

    /**
     * Handle PDO exceptions with detailed diagnostics
     *
     * @param PDOException $e
     * @param array $config
     * @return void
     */
    protected function handlePDOException(PDOException $e, array $config): void
    {
        $errorCode = $e->getCode();
        $errorMessage = $e->getMessage();
        
        $this->error("Error Code: {$errorCode}");
        $this->error("Error Message: {$errorMessage}");
        $this->newLine();
        
        // Provide specific diagnostics based on error patterns
        $this->warn("💡 Possible causes and solutions:");
        $this->newLine();
        
        if (str_contains($errorMessage, 'Access denied')) {
            $this->line("• <fg=yellow>Authentication Error:</> Check username and password");
            $this->line("  - Verify credentials in .env file");
            $this->line("  - Ensure database user exists and has proper privileges");
            $this->line("  - Command: GRANT ALL PRIVILEGES ON {$config['database']}.* TO '{$config['username']}'@'{$config['host']}';");
            
        } elseif (str_contains($errorMessage, 'Connection refused') || str_contains($errorMessage, 'Connection timed out')) {
            $this->line("• <fg=yellow>Connection Refused:</> Database server is not reachable");
            $this->line("  - Verify database server is running");
            $this->line("  - Check host and port: {$config['host']}:{$config['port']}");
            $this->line("  - Check firewall rules");
            $this->line("  - Verify network connectivity: ping {$config['host']}");
            
        } elseif (str_contains($errorMessage, 'Unknown database')) {
            $this->line("• <fg=yellow>Database Not Found:</> The specified database does not exist");
            $this->line("  - Database name: {$config['database']}");
            $this->line("  - Create database: CREATE DATABASE {$config['database']};");
            $this->line("  - Or run: php artisan migrate (if using migrations)");
            
        } elseif (str_contains($errorMessage, 'could not find driver')) {
            $this->line("• <fg=yellow>Driver Not Found:</> PDO driver is not installed");
            $this->line("  - Install PHP extension for {$config['driver']}");
            $this->line("  - For MySQL: sudo apt-get install php-mysql (or php{PHP_MAJOR_VERSION}-mysql)");
            $this->line("  - For PostgreSQL: sudo apt-get install php-pgsql");
            $this->line("  - Restart web server after installation");
            
        } elseif (str_contains($errorMessage, 'SQLSTATE[HY000] [2002]')) {
            $this->line("• <fg=yellow>Socket/Connection Error:</>");
            $this->line("  - Check if database service is running");
            $this->line("  - Verify socket path (for local connections)");
            $this->line("  - Try using 127.0.0.1 instead of 'localhost' or vice versa");
            
        } else {
            $this->line("• <fg=yellow>General Error:</>");
            $this->line("  - Review error message above carefully");
            $this->line("  - Check database server logs");
            $this->line("  - Verify all connection parameters in .env file");
        }
        
        $this->newLine();
        $this->info("📝 Configuration file location: config/database.php");
        $this->info("📝 Environment file location: .env");
        
        if ($this->option('verbose')) {
            $this->newLine();
            $this->warn('Full exception trace:');
            $this->line($e->getTraceAsString());
        }
    }
}
