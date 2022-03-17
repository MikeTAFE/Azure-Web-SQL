<?php

    /*
     * WEB-BASED SQL TOOL FOR AZURE
     * 
     * Author: Michael Kirkwood-Smith
     * 
     * This is a really simple PHP-based tool for interacting with a MySQL database via an Azure web app service.
     * Often you will not have direct access to the MySQL server (e.g. via port 3306), and must execute queries via PHP.
     * 
     * Fill in your database server details below where the <<<PLACEHOLDERS>>> are. These values come from the Azure Portal.
     * 
     * NOTE: You need to download the appropriate SSL/TLS security certificate to connect to MySQL via an encrypted connection:
     *       1. Download the SSL/TLS certificate
     *       2. Upload it to your server (I recommend the "site" directory as it is OUTSIDE the "wwwroot" directory)
     *       3. Make sure the path below is correct (relative to the location of this PHP file).
     * 
     * You use this tool at your own risk. I do not give any quarantee about compatibility or security.
     * 
    */

    // This is the secret key to protect the unathorised use of this very dangerous script file!
    // When enabled, you must add the security key to the query string, e.g. https://mysite.com/azure-web-based-sql-tool/?key=xxx
    // To disable this feature, comment out this line.
    define("SECURITY_KEY", "dd68db23-7be8-4bd2-802d-2c3261108afa");

    // DB config
    if (!($_SERVER['SERVER_NAME'] === "localhost" || $_SERVER['SERVER_NAME'] === "127.0.0.1")) {

        // REMOTE Azure database environment
        $dbConfig = (object)[
            "host" => "<<<SERVER>>>.mysql.database.azure.com",
            "username" => "<<<USERNAME>>>",
            "password" => "<<<PASSWORD>>>",
            "database" => "<<<DATABASE>>>",
            "options" => [
                PDO::ATTR_ERRMODE        => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE    => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_SSL_KEY  => __DIR__ . '/../../DigiCertGlobalRootCA.crt.pem',
                // PDO::MYSQL_ATTR_SSL_CA  => '/etc/mysql/ssl/ca-cert.pem',
            ],
        ];

    } else {

        // LOCAL environment (e.g. XAMPP) for testing before deployment
        $dbConfig = (object)[
            "host" => "localhost",
            "username" => "root",
            "password" => "",
            "database" => "testing",
            "options" => [
                PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE    => PDO::FETCH_ASSOC,
            ],
        ];

    }

    // PHP references
    require_once "./php/_includes.php";

    // Security check
    if (defined('SECURITY_KEY') && ($_GET['key'] ?? "") !== SECURITY_KEY) {
        echo "SECURITY KEY ERROR - add the correct security key to the query string, e.g. https://mysite.com/azure-web-based-sql-tool/?key=xxx";
        exit;
    }

    // Get user data
    $sqlQuery = trim($_POST['sql'] ?? "");

    // Connect to database
    $dbConnected = Helper::dbConnect($dbConfig);

    // Check if query should be executed
    if ($dbConnected && isset($_POST['execute']) && !empty($sqlQuery)) {
        
        // Check if SELECT query... (must be very first chars in SQL query)
        if (preg_match('/^SELECT.*$/', $sqlQuery)) {
        
            $rows = Helper::executeQuery($sqlQuery);

            if (!empty($rows)) {

                // Row count
                $rowCount = count($rows);

                // Construct HTML data table
                $dataTable = Helper::arrayToHtmlTable($rows);

                // Display message
                Helper::$successMessages[] = "<p>Rows returned: {$rowCount}</p><div class='table-container'>{$dataTable}</div>";

            } else {
                Helper::$errorMessages[] = "<p>No rows returned.</p>";
            }

        } else {
        
            $rowCount = Helper::executeNonQuery($sqlQuery);

            if (is_int($rowCount)) {
                Helper::$successMessages[] = "Rows affected: $rowCount";
            } else {
                Helper::$errorMessages[] = "No row count returned.";
            }

        }
    }

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Azure SQL tool</title>
    <link rel="stylesheet" href="./styles/style.css">
    <link rel="apple-touch-icon" sizes="180x180" href="./icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./icons/favicon-16x16.png">
    <link rel="manifest" href="./icons/site.webmanifest">
</head>
<body>
    <div class="site-wrapper">
        <h1>Azure SQL tool</h1>
        <p>This is a simple website to allow writing SQL statements via the web to a TAFE Azure instance. It connects to a MySQL database instance and executes SQL commands without using a local MySQL client (e.g. MySQL Workbench) to avoid network security issues.</p>

        <div class="message warning">
            <p><strong>WARNING:</strong> This is a <em>VERY</em> dangerous file to leave exposed on your web server as it allows <em>ANYONE</em> to run abitrary SQL commands against your database!</p>
        </div>

        <?php foreach (Helper::$errorMessages as $errorMessage): ?>
            <div class="message error">
                <?= $errorMessage ?>
            </div>
        <?php endforeach; ?>

        <?php foreach (Helper::$successMessages as $successMessage): ?>
            <div class="message success">
                <?= $successMessage ?>
            </div>
        <?php endforeach; ?>

        <h2>Execute a query (against the <?= e($dbConfig->database) ?> database)</h2>

        <form action="" method="post" class="theme--dark">

            <div class="form-group">
                <label for="sql">SQL query to execute</label>
                <textarea name="sql" id="sql" cols="30" rows="10" aria-label="SQL query to execute" placeholder="Example: SELECT * FROM somewhere;"><?= e($sqlQuery) ?></textarea>
            </div>

            <div class="form-group">
                <button type="submit" name="execute" class="button primary">⚠️ Run query</button>
            </div>

        </form>

        <footer class="site-footer">
            <p><small>Copyright &copy;<?= date('Y') ?> Michael Kirkwood-Smith</small></p>
        </footer>
    </div>
</body>
</html>