/**
 * Processes the payment by gathering the already computed balance
 * and the student details to send to the backend.
 */
function processPayment() {
    // Grab the Student ID
    const studentId = document.getElementById('student-id').value;
    
    // Grab the balance already computed by otherPayments.js
    const totalToPay = document.getElementById('balance-display').innerText;

    // Basic validation before sending
    if (!studentId) {
        alert("Please search for a Student ID first.");
        return;
    }

    if (parseFloat(totalToPay) <= 0) {
        alert("The balance is 0.00. Please select fees or add other payments.");
        return;
    }

    // Confirmation dialog
    if (confirm(`Proceed with payment of Php ${totalToPay} for Student ${studentId}?`)) {
        
        const payload = {
            studentId: studentId,
            amountPaid: totalToPay,

            fixedFees: Array.from(document.querySelectorAll('.fee-checkbox:checked'))
                            .map(cb => cb.closest('tr').cells[0].innerText),

            otherPayments: Array.from(document.querySelectorAll('.other-desc'))
                                .map(input => input.value).filter(val => val !== "")
        };

        fetch('handlePayment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Payment Successful! Receipt sent via email.");
                location.reload(); 
            } else {
                alert("Error processing payment: " + data.message);
            }
        })
        .catch(err => {
            console.error("Payment Error:", err);
            alert("A system error occurred. Please try again.");
        });
    }
}