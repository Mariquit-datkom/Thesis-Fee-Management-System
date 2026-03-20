document.querySelectorAll('.btn-generate-soa').forEach(button => {
    button.addEventListener('click', function() {
        const studentId = this.getAttribute('data-id');
        
        if (!studentId) {
            alert("Error: Student ID not found.");
            return;
        }

        // Optional: Disable button to prevent double clicks
        const originalText = this.innerHTML;
        this.disabled = true;
        this.innerHTML = "Generating...";

        // Send background request to PHP
        fetch(`generateSOA.php?id=${studentId}`)
            .then(response => response.json()) // We expect a JSON response now
            .then(data => {
                if (data.success) {
                    alert("Success! " + data.message);
                } else {
                    alert("Failed: " + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("An error occurred while generating the SOA.");
            })
            .finally(() => {
                // Re-enable button
                this.disabled = false;
                this.innerHTML = originalText;
            });
    });
});