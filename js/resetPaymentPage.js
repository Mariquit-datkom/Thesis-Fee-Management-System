function resetPaymentPage() {
    const studentInput = document.getElementById('student-id');
    if (studentInput) studentInput.value = '';

    const balanceDisplay = document.getElementById('balance-display');
    if (balanceDisplay) balanceDisplay.innerText = '0.00';

    const tableBody = document.querySelector('#fee-table tbody');
    if (tableBody) {
        tableBody.innerHTML = '<tr><td colspan="3">Please enter a Student ID to view fees.</td></tr>';
    }

    window.location.href = window.location.pathname; 
}