document.getElementById('add-payment-row').addEventListener('click', function() {
    const tbody = document.getElementById('other-payments-body');
    const row = document.createElement('tr');

    row.innerHTML = `
        <td style="border: 1px #53595f solid; padding: 5px;">
            <input type="text" class="other-desc" placeholder="e.g. ID Replacement" style="width: 100%; border: none; outline: none; font-size: 16px; padding: 7px;">
        </td>
        <td style="border: 1px #53595f solid; padding: 5px;">
            <input type="number" class="other-amount" placeholder="Php 0.00" style="width: 100%; border: none; outline: none; font-size: 16px; text-align: center; padding: 7px;">
        </td>
        <td style="border: 1px #53595f solid; padding: 5px; text-align: center;">
            <button type="button" class="btn-remove" style="background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer; padding: 2px 8px;">×</button>
        </td>
    `;

    tbody.appendChild(row);

    const amountInput = row.querySelector('.other-amount');
    amountInput.addEventListener('input', calculateGrandTotal);
    
    row.querySelector('.btn-remove').addEventListener('click', function() {
        row.remove();
        calculateGrandTotal();
    });
});

function calculateGrandTotal() {
    let total = 0;

    document.querySelectorAll('.fee-checkbox:checked').forEach(checkedBox => {
        total += parseFloat(checkedBox.getAttribute('data-price')) || 0;
    });

    document.querySelectorAll('.other-amount').forEach(input => {
        total += parseFloat(input.value) || 0;
    });

    document.getElementById('balance-display').innerText = total.toFixed(2);
}

document.querySelectorAll('.fee-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', calculateGrandTotal);
});