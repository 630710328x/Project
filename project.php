<?php
// Database connection details
$host = 'localhost';
$dbname = 'postgres';
$username = 'postgres';
$password = 'root';

try {
    // Connect to PostgreSQL database
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // List of tables to query
    $tables = [
        'public.ratanakosin'
    ];

    // Array to store all family trees
    $allFamilyTrees = [];

    // Fetch family tree data for each table
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SELECT id, name, parent_id, wife_id, husband_id, father_id, mother_id FROM $table ORDER BY parent_id, id");
        $stmt->execute();
        $familyTree = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Build the tree
        $tree = buildTree($familyTree);
        $allFamilyTrees[$table] = $tree;
    }

} catch (PDOException $e) {
    // Handle database connection error
    die("Database connection failed: " . $e->getMessage());
}

// Function to build the tree
function buildTree(array $elements, $parentId = null) {
    $branch = [];

    foreach ($elements as $element) {
        if ($element['parent_id'] == $parentId) {
            $element['wife'] = findMember($elements, $element['wife_id']);
            $element['husband'] = findMember($elements, $element['husband_id']);
            $element['father'] = findMember($elements, $element['father_id']);
            $element['mother'] = findMember($elements, $element['mother_id']);
            $children = buildTree($elements, $element['id']);
            if ($children) {
                $element['children'] = $children;
            }
            $branch[] = $element;
        }
    }

    return $branch;
}

// Function to find a family member by ID
function findMember($elements, $id) {
    foreach ($elements as $element) {
        if ($element['id'] == $id) {
            return $element;
        }
    }
    return null;
}

// Function to render the tree
function renderTree($tree, $isMainNode = true) {
    echo '<ul>';
    foreach ($tree as $node) {
        $mainNodeClass = $node['parent_id'] === null ? 'main-node' : '';
        echo "<li class='$mainNodeClass'>";

        // Render the main node and its parents above it
        echo '<div class="node-container">';
        if ($isMainNode && ($node['parent_id'] === null)) {
            if (isset($node['father']) || isset($node['mother'])) {
                echo '<div class="parents">';
                if (isset($node['father'])) {
                    echo '<div class="node parent">Father: ' . htmlspecialchars($node['father']['name']) . '</div>';
                }
                if (isset($node['mother'])) {
                    echo '<div class="node parent">Mother: ' . htmlspecialchars($node['mother']['name']) . '</div>';
                }
                echo '</div>';
            }
        }

        echo '<div class="node">';
        echo '<a href="#">' . htmlspecialchars($node['name']) . '</a>';
        if (isset($node['wife'])) {
            echo ' <span class="spouse">Wife: ' . htmlspecialchars($node['wife']['name']) . '</span>';
        }
        echo '</div>';
        echo '</div>'; // end of node-container

        if (isset($node['children'])) {
            renderTree($node['children'], false);
        }

        echo '</li>';
    }
    echo '</ul>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Tree</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php foreach ($allFamilyTrees as $tableName => $tree): ?>
    <div class="tree">
        <h2><?php echo htmlspecialchars($tableName); ?></h2>
        <?php renderTree($tree); ?>
    </div>
<?php endforeach; ?>
<script src="scripts.js"></script>
</body>
</html>