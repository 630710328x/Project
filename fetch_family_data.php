<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details
$host = 'localhost';
$db = 'postgres';  // Replace 'your_database' with the actual database name
$user = 'postgres'; // Replace with your actual database username
$pass = 'root'; // Replace with your actual database password

// Connect to PostgreSQL
$conn = pg_connect("host=$host dbname=$db user=$user password=$pass");

if (!$conn) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Connection failed: ' . pg_last_error()]);
    exit;
}

// Get the table name from the request
$table = $_GET['table'] ?? 'ratanakosin';

// Function to fetch data from a specified table
function fetch_family_data($conn, $table) {
    // Validate the table name to prevent SQL injection
    $allowed_tables = ['ratanakosin', 'lanchang'];
    if (!in_array($table, $allowed_tables)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid table name']);
        exit;
    }

    // Fetch family data from the specified table
    $query = "SELECT id, parent_id, name, relationship FROM public.$table";
    $result = pg_query($conn, $query);

    if (!$result) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Query failed: ' . pg_last_error()]);
        exit;
    }

    $family = array();
    while ($row = pg_fetch_assoc($result)) {
        $family[] = $row;
    }

    return $family;
}

// Fetch the family data
$family = fetch_family_data($conn, $table);
pg_close($conn);

// Encode data to JSON
header('Content-Type: application/json');
echo json_encode($family);
?>
