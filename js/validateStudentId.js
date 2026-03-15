function validateStudentId(input) {
    let value = input.value.replace(/\D/g, '');

    if (value.length > 4) {
        value = value.substring(0, 4) + '-' + value.substring(4, 7);
    }

    input.value = value.substring(0, 8);
}