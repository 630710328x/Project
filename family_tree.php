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
    die("Connection failed: " . pg_last_error());
}

// Function to fetch data from a specified table
function fetch_family_data($conn, $table) {
    // Validate the table name to prevent SQL injection
    $allowed_tables = ['ratanakosin', 'lanchang'];
    if (!in_array($table, $allowed_tables)) {
        die("Invalid table name");
    }

    // Fetch family data from the specified table
    $query = "SELECT id, parent_id, name, relationship FROM public.$table";
    $result = pg_query($conn, $query);

    if (!$result) {
        die("Query failed: " . pg_last_error());
    }

    $family = array();
    while ($row = pg_fetch_assoc($result)) {
        $family[] = $row;
    }

    return $family;
}

// Determine which table to load initially
$table = $_GET['table'] ?? 'ratanakosin';
$family = fetch_family_data($conn, $table);
pg_close($conn);

// Encode data to JSON
$family_json = json_encode($family);
if ($family_json === false) {
    die("JSON encoding failed: " . json_last_error_msg());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Tree</title>
    <script src="https://balkangraph.com/js/latest/OrgChart.js"></script>
    <style>
        #tree {
            width: 100%;
            height: 90vh;
            border: 1px solid #ccc;
        }
        #controls {
            margin: 10px;
        }
    </style>
</head>
<body>
    <div id="controls">
        <label for="tableSelect">Select Table: </label>
        <select id="tableSelect">
            <option value="ratanakosin" <?php if ($table == 'ratanakosin') echo 'selected'; ?>>Ratanakosin</option>
            <option value="lanchang" <?php if ($table == 'lanchang') echo 'selected'; ?>>Lanchang</option>
        </select>
    </div>
    <div id="tree"></div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tableSelect = document.getElementById('tableSelect');
            const treeContainer = document.getElementById('tree');

            const loadFamilyData = (table) => {
                fetch(`fetch_family_data.php?table=${table}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok ' + response.statusText);
                        }
                        return response.json();
                    })
                    .then(familyData => {
                        const nodes = familyData.map(member => ({
                            id: member.id,
                            pid: member.parent_id,
                            name: member.name,
                            relationship: member.relationship
                        }));

                        new OrgChart(treeContainer, {
                            nodes: nodes,
                            nodeBinding: {
                                field_0: "name",
                                field_1: "relationship"
                            }
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                    });
            };

            // Load initial data
            loadFamilyData(tableSelect.value);

            // Change event for the select box
            tableSelect.addEventListener('change', function() {
                loadFamilyData(this.value);
            });
        });
    </script>
</body>
</html>
