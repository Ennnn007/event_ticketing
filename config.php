<?php
require __DIR__ . '/vendor/autoload.php';

use Aws\SecretsManager\SecretsManagerClient;
use Aws\Exception\AwsException;

// Use legacy-style error reporting: mysqli functions return false on
// failure (e.g. a foreign-key violation) instead of throwing an exception,
// so ordinary "if (!$stmt->execute())" checks work as expected below.
mysqli_report(MYSQLI_REPORT_OFF);

// TAR UMT is in Malaysia (UTC+8), but this server's OS clock defaults to UTC
// (true both locally in Docker and on a stock EC2 instance) - without this,
// every date()/time() call here (booking date validation, "today" defaults,
// past-slot checks) runs 8 hours behind real local time.
date_default_timezone_set('Asia/Kuala_Lumpur');

// ============================================================================
// Database connection
// ============================================================================
// PRODUCTION (EC2/AWS): DB_SECRET_NAME is set as an Apache SetEnv, pointing
// at an AWS Secrets Manager secret that holds host/username/password/dbname
// as JSON. Credentials never live in plaintext on the instance.
//
// LOCAL / XAMPP fallback: when DB_SECRET_NAME isn't set, falls back to plain
// DB_HOST/DB_USER/DB_PASS/DB_NAME env vars (or the localhost defaults below),
// so local development is unaffected.
// ============================================================================
$secretName = getenv('DB_SECRET_NAME');

if ($secretName) {
    $client = new SecretsManagerClient([
        'region'  => getenv('AWS_REGION') ?: 'us-east-1',
        'version' => '2017-10-17',
    ]);

    try {
        $result = $client->getSecretValue(['SecretId' => $secretName]);
        $secret = json_decode($result['SecretString'], true);
        $host   = $secret['host'];
        $user   = $secret['username'];
        $pass   = $secret['password'];
        $dbname = $secret['dbname'];
    } catch (AwsException $e) {
        die('Failed to load DB credentials from Secrets Manager: ' . $e->getAwsErrorMessage());
    }
} else {
    $host   = getenv('DB_HOST') ?: 'localhost';
    $user   = getenv('DB_USER') ?: 'root';
    $pass   = getenv('DB_PASS') ?: '';
    $dbname = getenv('DB_NAME') ?: 'event_ticketing_db';
}

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Keep MySQL's NOW()/CURRENT_TIMESTAMP in step with the PHP timezone above -
// otherwise created_at/returned_at etc. would still be recorded 8 hours off.
$conn->query("SET time_zone = '+08:00'");