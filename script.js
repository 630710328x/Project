document.addEventListener("DOMContentLoaded", function() {
    fetch('fetch_tree.php')
        .then(response => response.json())
        .then(data => {
            // Process data to create a family tree
            // You can use D3.js or any other library to visualize the tree
            const members = data.members;
            const relationships = data.relationships;

            // Example visualization code using D3.js
            const width = 800, height = 600;
            const svg = d3.select("#family-tree")
                          .append("svg")
                          .attr("width", width)
                          .attr("height", height);

            // Process and visualize data here
            // ...
        });
});