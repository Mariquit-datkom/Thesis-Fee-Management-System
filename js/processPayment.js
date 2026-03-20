/**
 * Revised processPayment.js
 * Captures detailed fee objects and sends them to handlePayment.php
 */
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
        
        // Collect Fixed Fees (Name and Amount)
        const fixedFees = Array.from(document.querySelectorAll('.fee-checkbox:checked'))
            .map(cb => {
                const row = cb.closest('tr');
                return {
                    name: row.cells[0].innerText, // From payment.php table
                    amount: cb.getAttribute('data-price') 
                };
            });

        // Collect Other Payments (Name and Amount)
        const otherPayments = Array.from(document.querySelectorAll('#other-payments-body tr'))
            .map(row => {
                const descInput = row.querySelector('.other-desc');
                const amountInput = row.querySelector('.other-amount');
                if (descInput && descInput.value.trim() !== "") {
                    return {
                        name: descInput.value,
                        amount: amountInput.value || "0.00"
                    };
                }
                return null;
            }).filter(item => item !== null);

        // Merge all line items for the receipt
        const allItems = [...fixedFees, ...otherPayments];

        const payload = {
            studentId: studentId,
            totalAmount: totalToPay,
            items: allItems
        };

        fetch('handlePayment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Payment Successful! Receipt saved to assets/docs/receipts/");
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