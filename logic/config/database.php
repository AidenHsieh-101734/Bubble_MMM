<?php

$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_USER = getenv('DB_USER') ?: 'db101734';
$DB_PASS = getenv('DB_PASS') ?: 'CoreCorrupted69!';
$DB_NAME = getenv('DB_NAME') ?: '101734_BEROEPS2';

if (!class_exists('Database')) {
    class Database
    {
        private $host;
        private $db_name;
        private $username;
        private $password;
        private $conn;

        public function __construct()
        {
            global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;

            $this->host = $DB_HOST;
            $this->db_name = $DB_NAME;
            $this->username = $DB_USER;
            $this->password = $DB_PASS;
        }

        /**
         * Get database connection
         * @return PDO
         */
        public function getConnection()
        {
            $this->conn = null;

            try {
                $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";

                $this->conn = new PDO($dsn, $this->username, $this->password);

                // Set PDO error mode to exception
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Set default fetch mode to associative array
                $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                // Disable emulated prepared statements
                $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            } catch (PDOException $e) {
                error_log("Database Connection Error: " . $e->getMessage());
                throw new Exception("Database connection failed");
            }

            return $this->conn;
        }

        /**
         * Close database connection
         */
        public function closeConnection()
        {
            $this->conn = null;
        }
    }
}
