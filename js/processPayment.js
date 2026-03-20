function processPayment() {
    const payBtn = document.querySelector('.pay-btn');
    const originalText = payBtn.value; // Use .value if it's an <input type="button">

    // 1. Immediate Visual Feedback
    payBtn.disabled = true;
    payBtn.value = "Processing..."; 

    // 2. Small delay to ensure the browser UI updates before confirm() blocks the thread
    setTimeout(() => {
        const studentId = document.getElementById('student-id').value;
        const totalToPay = document.getElementById('balance-display').innerText;

        // Function to reset button if something goes wrong or user cancels
        const resetBtn = () => {
            payBtn.disabled = false;
            payBtn.value = originalText;
        };

        // Validation Checks
        if (!studentId) {
            alert("Please search for a Student ID first.");
            resetBtn();
            return;
        }

        if (parseFloat(totalToPay) <= 0) {
            alert("The balance is 0.00. Please select fees or add other payments.");
            resetBtn();
            return;
        }

        // 3. User Confirmation
        if (!confirm(`Proceed with payment of Php ${totalToPay} for Student ${studentId}?`)) {
            resetBtn();
            return;
        }

        // 4. Data Collection
        const allItems = [];
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

        document.querySelectorAll('#other-payments-body tr').forEach(row => {
            const descInput = row.querySelector('.other-desc');
            const amountInput = row.querySelector('.other-amount');
            if (descInput && descInput.value.trim() !== "") {
                const amount = parseFloat(amountInput.value) || 0;
                if (amount > 0) {
                    allItems.push({
                        name: descInput.value.trim(),
                        amount: amount.toFixed(2),
                        isFull: false
                    });
                }
            }
        });

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
                resetBtn();
            }
        })
        .catch(err => {
            console.error("Payment Error:", err);
            alert("A system error occurred.");
            resetBtn();
        });
    }, 10); // Increased to 100ms to give the browser more time to render
}