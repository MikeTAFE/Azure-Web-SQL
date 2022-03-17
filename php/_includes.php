<?php

    /*
     * WEB-BASED SQL TOOL FOR AZURE
     * 
     * Author: Michael Kirkwood-Smith
     * 
     * These are included resources for the PHP code - just to get some code separation happening!
     * 
    */

    // Prevent direct loading/opening of this file
    if (count(get_included_files()) === 1) exit("Direct access not permitted.");


    /**
     * General helper class for reusable functionality
     */
    class Helper
    {

        private static $_pdo = null;
        public static $errorMessages = [];
        public static $successMessages = [];

        /**
         * Convert an associative array into an HTML table.
         *
         * @param array $data The associative array of data to convert.
         * @return string The HTML table output.
         */
        public static function arrayToHtmlTable($data)
        {
    
            // Check if non-empty array
            if (!(is_array($data) && count($data) > 0)) return "";
    
            // Start table
            $output = "<table><tr>";
            
            // Get headers
            foreach (array_keys($data[0]) as $value) {
                $output .= "<th>" . e($value) . "</th>";
            }
            
            // Get each row
            foreach ($data as $row) {
                
                // Row separator
                $output .= "</tr><tr>";
                
                // Get data
                foreach ($row as $value) {
                    $output .= "<td>" . e($value) . "</td>";
                }
            }
            
            // End table
            $output .= "</tr></table>";
    
            return $output;
        }
    
    
        /**
         * Log an error message into the log file.
         *
         * @param string $message The error message to log.
         * @return void
         */
        public static function logErrorMessage($message)
        {
            file_put_contents(
                "site-error.log",
                date('Y-m-d H:i:s') . ': ' . $message . PHP_EOL,
                FILE_APPEND
            );
        }
    
        /**
         * Connect to the database using the DB config passed.
         *
         * @param object $dbConfig The DB connection configuration object. {host: "", username: "", password: "", database: "", options: []}
         * @return void
         */
        public static function dbConnect($dbConfig)
        {
            // Database connection
            try
            {
                // DB connection
                Helper::$_pdo = new PDO(
                    "mysql:host={$dbConfig->host};dbname={$dbConfig->database};charset=UTF8",
                    $dbConfig->username,
                    $dbConfig->password,
                    $dbConfig->options
                );
            }
            catch (Exception $ex)
            {
                Helper::$errorMessages[] = "Database connection failed - check the log.";
                // Helper::logErrorMessage("Database connection failed: " . $ex->getMessage());
                Helper::logErrorMessage("Database connection failed: " . $ex);

                return false;
            }

            return true;
        }
    
        /**
         * Execute a SELECT query.
         *
         * @param string $sql The SQL statement to execute.
         * @return array The returned data as an array.
         */
        public static function executeQuery($sql) 
        {
            $data = null;
    
            // Run query
            try
            {
                // Database query
                $stmt = Helper::$_pdo->prepare($sql);
                $stmt->execute();
                $data = $stmt->fetchAll();
    
            }
            catch (Exception $ex)
            {
                // Helper::$errorMessages[] = "Database query failed - check the log.";
                Helper::$errorMessages[] = "Database query failed: " . $ex->getMessage();
                Helper::logErrorMessage("Database query failed: " . $ex->getMessage());
            }
    
            return $data;
        }
    
        /**
         * Execute a non-query (action query), e.g. INSERT, UPDATE, DELETE, CREATE.
         *
         * @param string $sql The SQL statement to execute.
         * @return int The row count after executing the query.
         */
        public static function executeNonQuery($sql) 
        {
            $count = null;
    
            // Run query
            try
            {
                // Database query
                $stmt = Helper::$_pdo->prepare($sql);
                $stmt->execute();
                $count = $stmt->rowCount();
    
            }
            catch (Exception $ex)
            {
                // Helper::$errorMessages[] = "Database query failed - check the log.";
                Helper::$errorMessages[] = "Database query failed: " . $ex->getMessage();
                Helper::logErrorMessage("Database query failed: " . $ex->getMessage());
            }
    
            return $count;
        }

    }
    
    /**
     * Escape for HTML output - alias for htmlentities().
     *
     * @param string $value The raw input value.
     * @return string The escaped output.
     */
    function e($value)
    {
        return htmlentities($value);
    }
