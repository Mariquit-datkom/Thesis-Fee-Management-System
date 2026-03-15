document.querySelectorAll('.fee-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        let total = 0;
        document.querySelectorAll('.fee-checkbox:checked').forEach(checkedBox => {
            total += parseFloat(checkedBox.getAttribute('data-price'));
        });
        document.getElementById('balance-display').innerText = total.toFixed(2);
    });
});