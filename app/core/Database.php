<?php
/**
 * Database — PDO singleton connection wrapper.
 * All queries across the app go through this class using prepared statements
 * to guarantee protection against SQL injection.
 */
class Database
{
    private static ?PDO $instance = null;

    public static function connect(): PDO
    {
        if (self::$instance === null) {
            $host    = Env::get('DB_HOST', '127.0.0.1');
            $port    = Env::get('DB_PORT', '3306');
            $dbname  = Env::get('DB_NAME', 'aquaflow');
            $user    = Env::get('DB_USER', 'root');
            $pass    = Env::get('DB_PASS', '');
            $charset = 'utf8mb4';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Emulated prepares (still fully parameter-bound, still immune to SQL
                // injection) so the same named placeholder can be reused multiple times
                // in a single query (e.g. a search clause matching several columns).
                // True "native" prepares reject repeated named parameters in MySQL.
                PDO::ATTR_EMULATE_PREPARES   => true,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Never leak credentials or raw exception details to the client
                error_log('[AquaFlow] DB connection failed: ' . $e->getMessage());
                http_response_code(500);
                die('Database connection error. Please check your configuration or contact the administrator.');
            }
        }

        return self::$instance;
    }
}
