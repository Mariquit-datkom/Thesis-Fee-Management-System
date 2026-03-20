function sortTable(n) {
    let table = document.getElementById("studentTable");
    let rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
    switching = true;
    dir = "asc"; 
    
    while (switching) {
        switching = false;
        rows = table.rows;
        for (i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            x = rows[i].getElementsByTagName("TD")[n];
            y = rows[i + 1].getElementsByTagName("TD")[n];
            
            let xContent = x.textContent.toLowerCase();
            let yContent = y.textContent.toLowerCase();

            if (dir == "asc") {
                if (xContent > yContent) {
                    shouldSwitch = true;
                    break;
                }
            } else if (dir == "desc") {
                if (xContent < yContent) {
                    shouldSwitch = true;
                    break;
                }
            }
        }
        if (shouldSwitch) {
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            switchcount++;      
        } else {
            if (switchcount == 0 && dir == "asc") {
                dir = "desc";
                switching = true;
            }
        }
    }
}

function filterTable() {
    const input = document.getElementById("student-id");
    const filter = input.value.toUpperCase();
    const table = document.getElementById("studentTable");
    const tr = table.getElementsByTagName("tr");

    // Loop through all table rows, starting from index 1 to skip the header
    for (let i = 1; i < tr.length; i++) {
        // Get the Student ID cell and Full Name cell
        const idCell = tr[i].getElementsByTagName("td")[0];
        const nameCell = tr[i].getElementsByTagName("td")[1];
        
        if (idCell || nameCell) {
            const idValue = idCell.textContent || idCell.innerText;
            const nameValue = nameCell.textContent || nameCell.innerText;

            // Check if the input matches either the ID or the Name
            if (idValue.toUpperCase().indexOf(filter) > -1 || 
                nameValue.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = ""; // Show row
            } else {
                tr[i].style.display = "none"; // Hide row
            }
        }
    }
}