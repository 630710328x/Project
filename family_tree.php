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
            height: 95vh;
            border: 1px solid #ccc;
        }
        #controls {
            margin: 10px;
        }
        header nav ul {
            list-style-type: none;
            padding: 0;
            display: flex;
            gap: 10px;
        }
        header nav ul li {
            display: inline;
        }
        header nav ul li a {
            text-decoration: none;
            padding: 5px 10px;
            background-color: #ccc;
            border: 1px solid #999;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="family_tree.php">Family Tree</a></li>
            </ul>
        </nav>
    </header>
    <div id="controls">
        <label for="tableSelect">Select Table: </label>
        <select id="tableSelect">
            <option value="ratanakosin">Ratanakosin</option>
            <option value="lanchang">Lanchang</option>
        </select>
    </div>
    <div id="tree"></div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tableSelect = document.getElementById('tableSelect');
            const treeContainer = document.getElementById('tree');

            const loadFamilyData = (table) => {
                fetch(`fetch_family_data.php?table=${table}`)
                    .then(response => response.json())
                    .then(familyData => {
                        if (familyData.error) {
                            console.error(familyData.error);
                            return;
                        }

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
