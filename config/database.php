<?php
class Database {
    private static ?PDO $instance = null;

    private const DB_HOST = 'localhost';
    private const DB_PORT = '3306';
    private const DB_NAME = 'autoentrepreneur';
    private const DB_USER = 'root';
    private const DB_PASS = '';
    private const DB_CHARSET = 'utf8mb4';

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=%s;charset=%s',
                self::DB_NAME, self::DB_CHARSET
            );
            self::$instance = new PDO($dsn, self::DB_USER, self::DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$instance;
    }
}
