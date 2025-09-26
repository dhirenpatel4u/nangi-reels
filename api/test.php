<?php
// Set your Neon database credentials
$host = 'ep-bold-bush-adxtshtv-pooler.c-2.us-east-1.aws.neon.tech';  // Replace with your host
$dbname = 'neondb';  // Replace with your database name
$user = 'neondb_owner';  // Replace with your username
$password = 'npg_rKWPq0Vm1Gub';  // Replace with your password
$sslmode = 'require';  // SSL mode for secure connection

try {
    // Create the connection string (DSN)
    $dsn = "pgsql:host=$host;port=5432;dbname=$dbname;sslmode=$sslmode";

    // Attempt to connect to the PostgreSQL database
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // If successful, this will print a success message
    echo "Connection successful!";
} catch (PDOException $e) {
    // If there is an error, it will be caught here and displayed
    echo "Connection failed: " . $e->getMessage();
}
?>
