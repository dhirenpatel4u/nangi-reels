<?php
try {
    $host = 'ep-bold-bush-adxtshtv-pooler.c-2.us-east-1.aws.neon.tech';
    $dbname = 'neondb';
    $user = 'neondb_owner';
    $password = 'npg_rKWPq0Vm1Gub';
    $sslmode = 'require';
    $endpoint_id = 'ep-bold-bush-adxtshtv-pooler'; // Your actual endpoint ID

    $dsn = "pgsql:host=$host;port=5432;dbname=$dbname;sslmode=$sslmode;options='--endpoint=$endpoint_id'";

    $pdo = new PDO($dsn, $user, $password);
    echo "Connected successfully!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
