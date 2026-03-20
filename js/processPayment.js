function processPayment() {
    const studentId = document.getElementById('student-id').value;
    const totalToPay = document.getElementById('balance-display').innerText;

    if (!studentId) {
        alert("Please search for a Student ID first.");
        return;
    }

    if (parseFloat(totalToPay) <= 0) {
        alert("The balance is 0.00. Please select fees or add other payments.");
        return;
    }

    if (confirm(`Proceed with payment of Php ${totalToPay} for Student ${studentId}?`)) {
        
        // 1. Initialize the array FIRST
        const allItems = [];

        // 2. Collect Fixed Fees (Loop through the table rows)
        document.querySelectorAll('.fee-row').forEach(row => {
            const feeName = row.cells[0].innerText;
            const fullPayCheckbox = row.querySelector('.full-pay-checkbox');
            const partialInput = row.querySelector('.partial-amount-input');
            
            let paymentAmount = 0;
            let isFull = false;

            if (fullPayCheckbox && fullPayCheckbox.checked) {
                paymentAmount = parseFloat(fullPayCheckbox.getAttribute('data-price'));
                isFull = true;
            } else if (partialInput && parseFloat(partialInput.value) > 0) {
                paymentAmount = parseFloat(partialInput.value);
                isFull = false;
            }

            if (paymentAmount > 0) {
                allItems.push({
                    name: feeName,
                    amount: paymentAmount.toFixed(2),
                    isFull: isFull
                });
            }
        });

        // 3. Collect Other Payments and push them directly into allItems
        document.querySelectorAll('#other-payments-body tr').forEach(row => {
            const descInput = row.querySelector('.other-desc');
            const amountInput = row.querySelector('.other-amount');
            
            if (descInput && descInput.value.trim() !== "") {
                const amount = parseFloat(amountInput.value) || 0;
                if (amount > 0) {
                    allItems.push({
                        name: descInput.value.trim(),
                        amount: amount.toFixed(2),
                        isFull: false // Other payments are treated as standalone items
                    });
                }
            }
        });

        // 4. Create the payload
        const payload = {
            studentId: studentId,
            totalAmount: totalToPay,
            items: allItems
        };

        // 5. Send to PHP
        fetch('handlePayment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Payment Successful! Receipt saved.");
                location.reload(); 
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(err => {
            console.error("Payment Error:", err);
            alert("A system error occurred.");
        });
    }
}